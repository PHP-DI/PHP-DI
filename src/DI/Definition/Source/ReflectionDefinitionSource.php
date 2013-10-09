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
use DI\Definition\MethodInjection;
use ReflectionClass;
use ReflectionParameter;

/**
 * Reads DI class definitions using only reflection
 *
 * Will guess injection only on class constructors
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class ReflectionDefinitionSource implements DefinitionSource
{
    /**
     * {@inheritdoc}
     */
    public function getDefinition($name)
    {
        if (!$this->classExists($name)) {
            return null;
        }

        $reflectionClass = new ReflectionClass($name);

        $classDefinition = new ClassDefinition($name);

        // Constructor
        $constructor = $reflectionClass->getConstructor();

        if ($constructor && $constructor->isPublic()) {
            $parameters = array();
            foreach ($constructor->getParameters() as $parameter) {
                $parameterType = $this->getParameterType($parameter);
                if (!$parameterType) {
                    break;
                }
                $parameters[] = new EntryReference($parameterType);
            }

            $classDefinition->setConstructorInjection(
                new MethodInjection($constructor->name, $parameters)
            );
        }

        return $classDefinition;
    }

    /**
     * @param ReflectionParameter $parameter
     * @return string|null Type of the parameter
     */
    private function getParameterType(ReflectionParameter $parameter)
    {
        $reflectionClass = $parameter->getClass();
        if ($reflectionClass === null) {
            return null;
        }
        return $reflectionClass->name;
    }

    /**
     * @param string $class
     * @return bool
     */
    private function classExists($class)
    {
        return class_exists($class) || interface_exists($class);
    }
}
