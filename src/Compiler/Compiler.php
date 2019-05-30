<?php

declare(strict_types=1);

namespace DI\Compiler;

use DI\Definition\ArrayDefinition;
use DI\Definition\DecoratorDefinition;
use DI\Definition\Definition;
use DI\Definition\EnvironmentVariableDefinition;
use DI\Definition\Exception\InvalidDefinition;
use DI\Definition\FactoryDefinition;
use DI\Definition\ObjectDefinition;
use DI\Definition\Reference;
use DI\Definition\Source\DefinitionSource;
use DI\Definition\StringDefinition;
use DI\Definition\ValueDefinition;
use DI\DependencyException;
use DI\Proxy\ProxyFactory;
use InvalidArgumentException;
use PhpParser\Node\Expr\Closure;
use SuperClosure\Analyzer\AstAnalyzer;
use SuperClosure\Exception\ClosureAnalysisException;

/**
 * Compiles the container into PHP code much more optimized for performances.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class Compiler
{
    /**
     * @var string
     */
    private $containerClass;

    /**
     * @var string
     */
    private $containerParentClass;

    /**
     * Definitions indexed by the entry name. The value can be null if the definition needs to be fetched.
     *
     * Keys are strings, values are `Definition` objects or null.
     *
     * @var \ArrayIterator
     */
    private $entriesToCompile;

    /**
     * Map of entry names to method names.
     *
     * @var string[]
     */
    private $entryToMethodMapping = [];

    /**
     * @var string[]
     */
    private $methods = [];

    /**
     * @var bool
     */
    private $autowiringEnabled;

    /**
     * @var ProxyFactory
     */
    private $proxyFactory;

    public function __construct(ProxyFactory $proxyFactory)
    {
        $this->proxyFactory = $proxyFactory;
    }

    public function getProxyFactory() : ProxyFactory
    {
        return $this->proxyFactory;
    }

    /**
     * Compile the container.
     *
     * @return string The compiled container file name.
     */
    public function compile(
        DefinitionSource $definitionSource,
        string $directory,
        string $className,
        string $parentClassName,
        bool $autowiringEnabled
    ) : string {
        $fileName = rtrim($directory, '/') . '/' . $className . '.php';

        if (file_exists($fileName)) {
            // The container is already compiled
            return $fileName;
        }

        $this->autowiringEnabled = $autowiringEnabled;

        // Validate that a valid class name was provided
        $validClassName = preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $className);
        if (!$validClassName) {
            throw new InvalidArgumentException("The container cannot be compiled: `$className` is not a valid PHP class name");
        }

        $this->entriesToCompile = new \ArrayIterator($definitionSource->getDefinitions());

        // We use an ArrayIterator so that we can keep adding new items to the list while we compile entries
        foreach ($this->entriesToCompile as $entryName => $definition) {
            $silenceErrors = false;
            // This is an entry found by reference during autowiring
            if (!$definition) {
                $definition = $definitionSource->getDefinition($entryName);
                // We silence errors for those entries because type-hints may reference interfaces/abstract classes
                // which could later be defined, or even not used (we don't want to block the compilation for those)
                $silenceErrors = true;
            }
            if (!$definition) {
                // We do not throw a `NotFound` exception here because the dependency
                // could be defined at runtime
                continue;
            }
            // Check that the definition can be compiled
            $errorMessage = $this->isCompilable($definition);
            if ($errorMessage !== true) {
                continue;
            }
            try {
                $this->compileDefinition($entryName, $definition);
            } catch (InvalidDefinition $e) {
                if ($silenceErrors) {
                    // forget the entry
                    unset($this->entryToMethodMapping[$entryName]);
                } else {
                    throw $e;
                }
            }
        }

        $this->containerClass = $className;
        $this->containerParentClass = $parentClassName;

        ob_start();
        require __DIR__ . '/Template.php';
        $fileContent = ob_get_contents();
        ob_end_clean();

        $fileContent = "<?php\n" . $fileContent;

        $this->createCompilationDirectory(dirname($fileName));
        file_put_contents($fileName, $fileContent);

        return $fileName;
    }

    /**
     * @throws DependencyException
     * @throws InvalidDefinition
     * @return string The method name
     */
    private function compileDefinition(string $entryName, Definition $definition) : string
    {
        // Generate a unique method name
        $methodName = str_replace('.', '', uniqid('get', true));
        $this->entryToMethodMapping[$entryName] = $methodName;

        switch (true) {
            case $definition instanceof ValueDefinition:
                $value = $definition->getValue();
                $code = 'return ' . $this->compileValue($value) . ';';
                break;
            case $definition instanceof Reference:
                if ($definition->isServiceLocatorEntry()) {
                    $requestingEntry = $definition->getRequestingName();
                    $serviceLocatorDefinition = $definition->getServiceLocatorDefinition();
                    $code = 'return $this->resolveServiceLocator(' . $this->compileValue($requestingEntry) . ', ' . $this->compileValue($serviceLocatorDefinition::$serviceLocatorRepositoryClass) . ');';
                    break;
                }

                $targetEntryName = $definition->getTargetEntryName();
                $code = 'return $this->delegateContainer->get(' . $this->compileValue($targetEntryName) . ');';
                // If this method is not yet compiled we store it for compilation
                if (!isset($this->entriesToCompile[$targetEntryName])) {
                    $this->entriesToCompile[$targetEntryName] = null;
                }
                break;
            case $definition instanceof StringDefinition:
                $entryName = $this->compileValue($definition->getName());
                $expression = $this->compileValue($definition->getExpression());
                $code = 'return \DI\Definition\StringDefinition::resolveExpression(' . $entryName . ', ' . $expression . ', $this->delegateContainer);';
                break;
            case $definition instanceof EnvironmentVariableDefinition:
                $variableName = $this->compileValue($definition->getVariableName());
                $isOptional = $this->compileValue($definition->isOptional());
                $defaultValue = $this->compileValue($definition->getDefaultValue());
                $code = <<<PHP
        \$value = getenv($variableName);
        if (false !== \$value) return \$value;
        if (!$isOptional) {
            throw new \DI\Definition\Exception\InvalidDefinition("The environment variable '{$definition->getVariableName()}' has not been defined");
        }
        return $defaultValue;
PHP;
                break;
            case $definition instanceof ArrayDefinition:
                try {
                    $code = 'return ' . $this->compileValue($definition->getValues()) . ';';
                } catch (\Exception $e) {
                    throw new DependencyException(sprintf(
                        'Error while compiling %s. %s',
                        $definition->getName(),
                        $e->getMessage()
                    ), 0, $e);
                }
                break;
            case $definition instanceof ObjectDefinition:
                $compiler = new ObjectCreationCompiler($this);
                $code = $compiler->compile($definition);
                $code .= "\n        return \$object;";
                break;
            case $definition instanceof DecoratorDefinition:
                $decoratedDefinition = $definition->getDecoratedDefinition();
                if (! $decoratedDefinition instanceof Definition) {
                    if (! $definition->getName()) {
                        throw new InvalidDefinition('Decorators cannot be nested in another definition');
                    }
                    throw new InvalidDefinition(sprintf(
                        'Entry "%s" decorates nothing: no previous definition with the same name was found',
                        $definition->getName()
                    ));
                }
                $code = sprintf(
                    'return call_user_func(%s, %s, $this->delegateContainer);',
                    $this->compileValue($definition->getCallable()),
                    $this->compileValue($decoratedDefinition)
                );
                break;
            case $definition instanceof FactoryDefinition:
                $value = $definition->getCallable();

                // Custom error message to help debugging
                $isInvokableClass = is_string($value) && class_exists($value) && method_exists($value, '__invoke');
                if ($isInvokableClass && !$this->autowiringEnabled) {
                    throw new InvalidDefinition(sprintf(
                        'Entry "%s" cannot be compiled. Invokable classes cannot be automatically resolved if autowiring is disabled on the container, you need to enable autowiring or define the entry manually.',
                        $entryName
                    ));
                }

                $definitionParameters = '';
                if (!empty($definition->getParameters())) {
                    $definitionParameters = ', ' . $this->compileValue($definition->getParameters());
                }

                $code = sprintf(
                    'return $this->resolveFactory(%s, %s%s);',
                    $this->compileValue($value),
                    var_export($entryName, true),
                    $definitionParameters
                );

                break;
            default:
                // This case should not happen (so it cannot be tested)
                throw new \Exception('Cannot compile definition of type ' . get_class($definition));
        }

        $this->methods[$methodName] = $code;

        return $methodName;
    }

    public function compileValue($value) : string
    {
        // Check that the value can be compiled
        $errorMessage = $this->isCompilable($value);
        if ($errorMessage !== true) {
            throw new InvalidDefinition($errorMessage);
        }

        if ($value instanceof Definition) {
            // Give it an arbitrary unique name
            $subEntryName = uniqid('SubEntry');
            // Compile the sub-definition in another method
            $methodName = $this->compileDefinition($subEntryName, $value);
            // The value is now a method call to that method (which returns the value)
            return "\$this->$methodName()";
        }

        if (is_array($value)) {
            $value = array_map(function ($value, $key) {
                $compiledValue = $this->compileValue($value);
                $key = var_export($key, true);

                return "            $key => $compiledValue,\n";
            }, $value, array_keys($value));
            $value = implode('', $value);

            return "[\n$value        ]";
        }

        if ($value instanceof \Closure) {
            return $this->compileClosure($value);
        }

        return var_export($value, true);
    }

    private function createCompilationDirectory(string $directory)
    {
        if (!is_dir($directory) && !@mkdir($directory, 0777, true)) {
            throw new InvalidArgumentException(sprintf('Compilation directory does not exist and cannot be created: %s.', $directory));
        }
        if (!is_writable($directory)) {
            throw new InvalidArgumentException(sprintf('Compilation directory is not writable: %s.', $directory));
        }
    }

    /**
     * @return string|true If null is returned that means that the value is compilable.
     */
    private function isCompilable($value)
    {
        if ($value instanceof ValueDefinition) {
            return $this->isCompilable($value->getValue());
        }
        if ($value instanceof DecoratorDefinition) {
            if (empty($value->getName())) {
                return 'Decorators cannot be nested in another definition';
            }
        }
        // All other definitions are compilable
        if ($value instanceof Definition) {
            return true;
        }
        if ($value instanceof \Closure) {
            return true;
        }
        if (is_object($value)) {
            return 'An object was found but objects cannot be compiled';
        }
        if (is_resource($value)) {
            return 'A resource was found but resources cannot be compiled';
        }

        return true;
    }

    private function compileClosure(\Closure $closure) : string
    {
        $closureAnalyzer = new AstAnalyzer;

        try {
            $closureData = $closureAnalyzer->analyze($closure);
        } catch (ClosureAnalysisException $e) {
            if (stripos($e->getMessage(), 'Two closures were declared on the same line') !== false) {
                throw new InvalidDefinition('Cannot compile closures when two closures are defined on the same line', 0, $e);
            }

            throw $e;
        }

        /** @var Closure $ast */
        $ast = $closureData['ast'];

        // Force all closures to be static (add the `static` keyword), i.e. they can't use
        // $this, which makes sense since their code is copied into another class.
        $ast->static = true;

        // Check if the closure imports variables with `use`
        if (! empty($ast->uses)) {
            throw new InvalidDefinition('Cannot compile closures which import variables using the `use` keyword');
        }

        $code = (new \PhpParser\PrettyPrinter\Standard)->prettyPrint([$ast]);

        // Trim spaces and the last `;`
        $code = trim($code, "\t\n\r;");

        return $code;
    }
}
