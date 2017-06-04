<?php
declare(strict_types=1);

namespace DI;

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
     * Compile the container.
     *
     * @return string The compiled container class name.
     */
    public function compile(DefinitionSource $definitionSource, string $fileName) : string
    {
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
                $values = $this->compileArrayValues($definition);
                $values = implode('', $values);
                $code = "return [\n$values        ];";
                break;
            case $definition instanceof ObjectDefinition:
                $compiler = new ObjectCreationCompiler($this);
                $code = $compiler->compile($definition);
                $code .= "\n        return \$object;";
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
     * @return string[]
     */
    private function compileArrayValues(ArrayDefinition $definition) : array
    {
        $values = $definition->getValues();
        $keys = array_keys($values);

        $values = array_map(function ($value, $key) use ($definition) {
            try {
                $compiledValue = $this->compileValue($value);
            } catch (\Exception $e) {
                throw new DependencyException(sprintf(
                    'Error while compiling %s[%s]. %s',
                    $definition->getName(),
                    $key,
                    $e->getMessage()
                ), 0, $e);
            }

            return '            ' . $compiledValue . ",\n";
        }, $values, $keys);

        return $values;
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
        if ($value instanceof FactoryDefinition) {
            return 'A factory definition was found but factories cannot be compiled';
        }
        // All other definitions are compilable
        if ($value instanceof Definition) {
            return true;
        }
        if (is_array($value)) {
            $compilable = true;
            array_walk_recursive($value, function ($value) use (&$compilable) {
                // The if avoids unnecessary checks
                if ($compilable === true) {
                    $message = $this->isCompilable($value);
                    if ($message !== true) {
                        $compilable = $message;
                    }
                }
            });
            return $compilable;
        }
        if (is_object($value)) {
            return 'An object was found but objects cannot be compiled';
        }
        if (is_resource($value)) {
            return 'A resource was found but resources cannot be compiled';
        }

        return true;
    }
}
