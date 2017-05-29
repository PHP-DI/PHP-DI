<?php

declare(strict_types=1);

namespace DI;

use DI\Definition\AliasDefinition;
use DI\Definition\ArrayDefinition;
use DI\Definition\Definition;
use DI\Definition\EnvironmentVariableDefinition;
use DI\Definition\Helper\DefinitionHelper;
use DI\Definition\ObjectDefinition;
use DI\Definition\Source\DefinitionSource;
use DI\Definition\StringDefinition;
use DI\Definition\ValueDefinition;
use InvalidArgumentException;

/**
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
     * @return string File name
     */
    public function compile(DefinitionSource $definitionSource, string $compilationDirectory) : string
    {
        $fileName = $compilationDirectory . '/CompiledContainer.php';

        if (file_exists($fileName)) {
            // The container is already compiled
            return $fileName;
        }

        $definitions = $definitionSource->getDefinitions();

        // The name of the class must be unique to allow using multiple compiled containers
        // in the same process (for example for tests).
        $this->containerClass = uniqid('CompiledContainer');

        foreach ($definitions as $entryName => $definition) {
            if ($definition instanceof ObjectDefinition) {
                continue;
            }
            if ($definition instanceof ValueDefinition && is_object($definition->getValue())) {
                continue;
            }
            $this->compileDefinition($entryName, $definition);
        }

        ob_start();
        require __DIR__ . '/Compiler/Template.php';
        $fileContent = ob_get_contents();
        ob_end_clean();

        $fileContent = "<?php\n" . $fileContent;

        $this->createCompilationDirectory($compilationDirectory);
        file_put_contents($fileName, $fileContent);

        return $fileName;
    }

    /**
     * @return string The method name
     */
    private function compileDefinition(string $entryName, Definition $definition) : string
    {
        // Generate a unique method name
        $methodName = uniqid('get');
        $this->entryToMethodMapping[$entryName] = $methodName;

        switch (get_class($definition)) {
            case ValueDefinition::class:
                /** @var ValueDefinition $definition */
                $value = $definition->getValue();
                $code = 'return ' . $this->compileValue($value) . ';';
                break;
            case AliasDefinition::class:
                /** @var AliasDefinition $definition */
                $targetEntryName = $definition->getTargetEntryName();
                // TODO delegate container
                $code = 'return $this->get(' . $this->compileValue($targetEntryName) . ');';
                break;
            case StringDefinition::class:
                /** @var StringDefinition $definition */
                $entryName = $this->compileValue($definition->getName());
                $expression = $this->compileValue($definition->getExpression());
                // TODO delegate container
                $code = 'return \DI\Definition\StringDefinition::resolveExpression(' . $entryName . ', ' . $expression . ', $this);';
                break;
            case EnvironmentVariableDefinition::class:
                /** @var EnvironmentVariableDefinition $definition */
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
            case ArrayDefinition::class:
                /** @var ArrayDefinition $definition */
                $values = $definition->getValues();
                $values = array_map(function ($value) {
                    return '            ' . $this->compileValue($value) . ",\n";
                }, $values);
                $values = implode('', $values);
                $code = "return [\n$values        ];";
                break;
            default:
                throw new \Exception('Cannot compile definition of type ' . get_class($definition));
        }

        $this->methods[$methodName] = $code;

        return $methodName;
    }

    private function compileValue($value) : string
    {
        if ($value instanceof DefinitionHelper) {
            // Give it an arbitrary unique name
            $subEntryName = uniqid('SubEntry');
            $value = $value->getDefinition($subEntryName);
            // Compile the sub-definition in another method
            $methodName = $this->compileDefinition($subEntryName, $value);
            // The value is now a method call to that method (which returns the value)
            return "\$this->$methodName()";
        } elseif (is_object($value)) {
            throw new \Exception('Cannot compile an object');
        } elseif (is_resource($value)) {
            throw new \Exception('Cannot compile a resource');
        }

        return var_export($value, true);
    }

    private function createCompilationDirectory(string $directory)
    {
        if (!is_dir($directory) && !@mkdir($directory, 0777, true)) {
            throw new InvalidArgumentException(sprintf('Cache directory does not exist and cannot be created: %s.', $directory));
        }
        if (!is_writable($directory)) {
            throw new InvalidArgumentException(sprintf('Cache directory is not writable: %s.', $directory));
        }
    }
}
