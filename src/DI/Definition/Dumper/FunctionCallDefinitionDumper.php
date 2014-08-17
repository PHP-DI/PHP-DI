<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Definition\Dumper;

use DI\Definition\Definition;
use DI\Definition\EntryReference;
use DI\Definition\FunctionCallDefinition;
use DI\Reflection\CallableReflectionFactory;

/**
 * Dumps function call definitions.
 *
 * @since 4.2
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class FunctionCallDefinitionDumper implements DefinitionDumper
{
    /**
     * {@inheritdoc}
     */
    public function dump(Definition $definition)
    {
        if (! $definition instanceof FunctionCallDefinition) {
            throw new \InvalidArgumentException(sprintf(
                'This definition dumper is only compatible with FunctionCallDefinition objects, %s given',
                get_class($definition)
            ));
        }

        $callable = $definition->getCallable();

        $functionReflection = CallableReflectionFactory::fromCallable($callable);

        $functionName = $this->getFunctionName($functionReflection);
        $parameters = $this->dumpMethodParameters($definition, $functionReflection);

        return sprintf("%s(\n    %s\n)", $functionName, $parameters);
    }

    private function dumpMethodParameters(
        FunctionCallDefinition $definition,
        \ReflectionFunctionAbstract $functionReflection
    ) {
        $args = array();

        foreach ($functionReflection->getParameters() as $index => $parameter) {
            if ($definition && $definition->hasParameter($index)) {
                $value = $definition->getParameter($index);

                if ($value instanceof EntryReference) {
                    $args[] = sprintf('$%s = link(%s)', $parameter->getName(), $value->getName());
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
                } catch (\ReflectionException $e) {
                    // The default value can't be read through Reflection because it is a PHP internal class
                }
            }

            $args[] = sprintf('$%s = #UNDEFINED#', $parameter->getName());
        }

        return implode(PHP_EOL . '    ', $args);
    }

    private function getFunctionName(\ReflectionFunctionAbstract $reflectionFunction)
    {
        if ($reflectionFunction->isClosure()) {
            return sprintf(
                'closure defined in %s at line %d',
                $reflectionFunction->getFileName(),
                $reflectionFunction->getStartLine()
            );
        } elseif ($reflectionFunction instanceof \ReflectionMethod) {
            return sprintf(
                '%s::%s',
                $reflectionFunction->getDeclaringClass()->getName(),
                $reflectionFunction->getName()
            );
        }

        return $reflectionFunction->getName();
    }
}
