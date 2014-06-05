<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.io/PHP-DI/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Definition\Source;

use DI\Definition\ClassDefinition;
use DI\Definition\EntryReference;
use DI\Definition\ClassDefinition\MethodInjection;
use DI\Definition\FunctionCallDefinition;
use DI\Definition\MergeableDefinition;

/**
 * Reads DI class definitions using reflection.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class ReflectionDefinitionSource implements DefinitionSource, CallableDefinitionSource
{
    /**
     * {@inheritdoc}
     */
    public function getDefinition($name, MergeableDefinition $parentDefinition = null)
    {
        // Only merges with class definition
        if ($parentDefinition && (! $parentDefinition instanceof ClassDefinition)) {
            return null;
        }

        $className = $parentDefinition ? $parentDefinition->getClassName() : $name;

        if (!class_exists($className) && !interface_exists($className)) {
            return null;
        }

        $class = new \ReflectionClass($className);
        $definition = new ClassDefinition($name);

        // Constructor
        $constructor = $class->getConstructor();
        if ($constructor && $constructor->isPublic()) {
            $definition->setConstructorInjection(
                MethodInjection::constructor($this->getParametersDefinition($constructor))
            );
        }

        // Merge with parent
        if ($parentDefinition) {
            $definition = $parentDefinition->merge($definition);
        }

        return $definition;
    }

    /**
     * {@inheritdoc}
     */
    public function getCallableDefinition($callable)
    {
        if (is_array($callable)) {
            list($class, $method) = $callable;
            $reflection = new \ReflectionMethod($class, $method);
        } else {
            $reflection = new \ReflectionFunction($callable);
        }

        return new FunctionCallDefinition($callable, $this->getParametersDefinition($reflection));
    }

    /**
     * Read the type-hinting from the parameters of the function.
     */
    private function getParametersDefinition(\ReflectionFunctionAbstract $constructor)
    {
        $parameters = array();

        foreach ($constructor->getParameters() as $index => $parameter) {
            $parameterClass = $parameter->getClass();

            if ($parameterClass) {
                $parameters[$index] = new EntryReference($parameterClass->getName());
            }
        }

        return $parameters;
    }
}
