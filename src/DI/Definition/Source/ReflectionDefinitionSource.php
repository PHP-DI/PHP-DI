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
use DI\Definition\ClassInjection\MethodInjection;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;

/**
 * Reads DI class definitions using reflection.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class ReflectionDefinitionSource implements DefinitionSource, ClassDefinitionSource
{
    /**
     * {@inheritdoc}
     */
    public function getDefinition($name)
    {
        if (!class_exists($name) && !interface_exists($name)) {
            return null;
        }

        $class = new ReflectionClass($name);
        $classDefinition = new ClassDefinition($name);

        // Constructor
        $constructor = $class->getConstructor();

        if ($constructor && $constructor->isPublic()) {
            $classDefinition->setConstructorInjection($this->getMethodInjection($name, $constructor));
        }

        return $classDefinition;
    }

    /**
     * {@inheritdoc}
     */
    public function getPropertyInjection($entryName, ReflectionProperty $property)
    {
        // Nothing to guess on properties
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getMethodInjection($entryName, ReflectionMethod $method)
    {
        $parameters = array();

        foreach ($method->getParameters() as $parameter) {
            $parameterClass = $parameter->getClass();

            if ($parameterClass) {
                $parameters[$parameter->getName()] = new EntryReference($parameterClass->getName());
            }
        }

        return new MethodInjection($method->getName(), $parameters);
    }
}
