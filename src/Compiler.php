<?php

declare(strict_types=1);

namespace DI;

use BetterReflection\Reflection\ReflectionFunction;
use BetterReflection\SourceLocator\Exception\TwoClosuresOneLine;
use DI\Compiler\ObjectCreationCompiler;
use DI\Definition\AliasDefinition;
use DI\Definition\ArrayDefinition;
use DI\Definition\DecoratorDefinition;
use DI\Definition\Definition;
use DI\Definition\EnvironmentVariableDefinition;
use DI\Definition\Exception\InvalidDefinition;
use DI\Definition\FactoryDefinition;
use DI\Definition\Helper\DefinitionHelper;
use DI\Definition\ObjectDefinition;
use DI\Definition\Source\DefinitionSource;
use DI\Definition\StringDefinition;
use DI\Definition\ValueDefinition;
use InvalidArgumentException;
use PhpParser\Node\Expr\Closure;

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
     * Compile the container.
     *
     * @return string The compiled container class name.
     */
    public function compile(DefinitionSource $definitionSource, string $fileName, bool $autowiringEnabled) : string
    {
        $this->autowiringEnabled = $autowiringEnabled;
        $this->containerClass = basename($fileName, '.php');

        // Validate that it's a valid class name
        $validClassName = preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $this->containerClass);
        if (!$validClassName) {
            throw new InvalidArgumentException("The file in which to compile the container must have a name that is a valid class name: {$this->containerClass} is not a valid PHP class name");
        }

        if (file_exists($fileName)) {
            // The container is already compiled
            return $this->containerClass;
        }

        $definitions = $definitionSource->getDefinitions();

        foreach ($definitions as $entryName => $definition) {
            // Check that the definition can be compiled
            $errorMessage = $this->isCompilable($definition);
            if ($errorMessage !== true) {
                continue;
            }
            $this->compileDefinition($entryName, $definition);
        }

        ob_start();
        require __DIR__ . '/Compiler/Template.php';
        $fileContent = ob_get_contents();
        ob_end_clean();

        $fileContent = "<?php\n" . $fileContent;

        $this->createCompilationDirectory(dirname($fileName));
        file_put_contents($fileName, $fileContent);

        return $this->containerClass;
    }

    /**
     * @throws DependencyException
     * @throws InvalidDefinition
     * @return string The method name
     */
    private function compileDefinition(string $entryName, Definition $definition) : string
    {
        // Generate a unique method name
        $methodName = uniqid('get');
        $this->entryToMethodMapping[$entryName] = $methodName;

        switch (true) {
            case $definition instanceof ValueDefinition:
                $value = $definition->getValue();
                $code = 'return ' . $this->compileValue($value) . ';';
                break;
            case $definition instanceof AliasDefinition:
                $targetEntryName = $definition->getTargetEntryName();
                $code = 'return $this->delegateContainer->get(' . $this->compileValue($targetEntryName) . ');';
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
        if ($value instanceof DefinitionHelper) {
            $value = $value->getDefinition('');
        }

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
     * @return string|null If null is returned that means that the value is compilable.
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

            return 'A decorator definition was found but decorators cannot be compiled';
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

    private function compileClosure(\Closure $value) : string
    {
        try {
            $reflection = ReflectionFunction::createFromClosure($value);
        } catch (TwoClosuresOneLine $e) {
            throw new InvalidDefinition('Cannot compile closures when two closures are defined on the same line', 0, $e);
        }

        /** @var Closure $ast */
        $ast = $reflection->getAst();

        // Force all closures to be static (add the `static` keyword), i.e. they can't use
        // $this, which makes sense since their code is copied into another class.
        $ast->static = true;

        // Check if the closure imports variables with `use`
        if (! empty($ast->uses)) {
            throw new InvalidDefinition('Cannot compile closures which import variables using the `use` keyword');
        }

        $code = (new \PhpParser\PrettyPrinter\Standard)->prettyPrint([$reflection->getAst()]);

        // Trim spaces and the last `;`
        $code = trim($code, "\t\n\r;");

        return $code;
    }
}
