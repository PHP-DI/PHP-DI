<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Definition\Dumper;

use DI\Definition\ObjectDefinition;
use DI\Definition\ObjectDefinition\MethodInjection;
use DI\Definition\Definition;
use DI\Definition\EntryReference;
use ReflectionException;
use ReflectionMethod;

/**
 * Dumps object definitions.
 *
 * @since 4.1
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class ObjectDefinitionDumper implements DefinitionDumper
{
    /**
     * {@inheritdoc}
     */
    public function dump(Definition $definition)
    {
        if (! $definition instanceof ObjectDefinition) {
            throw new \InvalidArgumentException(sprintf(
                'This definition dumper is only compatible with ObjectDefinition objects, %s given',
                get_class($definition)
            ));
        }

        $className = $definition->getClassName();
        $classExist = class_exists($className) || interface_exists($className);

        // Class
        if (! $classExist) {
            $warning = '#UNKNOWN# ';
        } else {
            $class = new \ReflectionClass($className);
            $warning = $class->isInstantiable() ? '' : '#NOT INSTANTIABLE# ';
        }
        $str = sprintf('    class = %s%s', $warning, $className);

        // Scope
        $str .= "\n    scope = " . $definition->getScope();

        // Lazy
        $str .= "\n    lazy = " . var_export($definition->isLazy(), true);

        if ($classExist) {
            // Constructor
            $str .= $this->dumpConstructor($className, $definition);

            // Properties
            $str .= $this->dumpProperties($definition);

            // Methods
            $str .= $this->dumpMethods($className, $definition);
        }

        return sprintf("Object (\n%s\n)", $str);
    }

    private function dumpConstructor($className, ObjectDefinition $definition)
    {
        $str = '';

        $constructorInjection = $definition->getConstructorInjection();

        if ($constructorInjection !== null) {
            $parameters = $this->dumpMethodParameters($className, $constructorInjection);

            $str .= sprintf("\n    __construct(\n        %s\n    )", $parameters);
        }

        return $str;
    }

    private function dumpProperties(ObjectDefinition $definition)
    {
        $str = '';

        foreach ($definition->getPropertyInjections() as $propertyInjection) {
            $value = $propertyInjection->getValue();
            if ($value instanceof EntryReference) {
                $valueStr = sprintf('get(%s)', $value->getName());
            } else {
                $valueStr = var_export($value, true);
            }

            $str .= sprintf("\n    $%s = %s", $propertyInjection->getPropertyName(), $valueStr);
        }

        return $str;
    }

    private function dumpMethods($className, ObjectDefinition $definition)
    {
        $str = '';

        foreach ($definition->getMethodInjections() as $methodInjection) {
            $parameters = $this->dumpMethodParameters($className, $methodInjection);

            $str .= sprintf("\n    %s(\n        %s\n    )", $methodInjection->getMethodName(), $parameters);
        }

        return $str;
    }

    private function dumpMethodParameters($className, MethodInjection $methodInjection)
    {
        $methodReflection = new \ReflectionMethod($className, $methodInjection->getMethodName());

        $args = array();

        foreach ($methodReflection->getParameters() as $index => $parameter) {
            if ($methodInjection->hasParameter($index)) {
                $value = $methodInjection->getParameter($index);

                if ($value instanceof EntryReference) {
                    $args[] = sprintf('$%s = get(%s)', $parameter->getName(), $value->getName());
                } else {
                    $args[] = sprintf('$%s = %s', $parameter->getName(), var_export($value, true));
                }
                continue;
            }

            // If the parameter is optional and wasn't specified, we take its default value
            if ($parameter->isOptional()) {
                try {
                    $value = $parameter->getDefaultValue();

                    $args[] = sprintf(
                        '$%s = (default value) %s',
                        $parameter->getName(),
                        var_export($value, true)
                    );
                    continue;
                } catch (ReflectionException $e) {
                    // The default value can't be read through Reflection because it is a PHP internal class
                }
            }

            $args[] = sprintf('$%s = #UNDEFINED#', $parameter->getName());
        }

        return implode(PHP_EOL . '        ', $args);
    }
}
