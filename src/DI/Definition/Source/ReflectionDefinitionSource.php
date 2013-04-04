<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.com/PHP-DI/
 * @copyright 2012 Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Definition\Source;

use DI\Definition\ClassDefinition;
use DI\Definition\MethodInjection;
use DI\Definition\ParameterInjection;
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

            $constructorInjection = new MethodInjection($constructor->name);
            $classDefinition->setConstructorInjection($constructorInjection);

            foreach ($constructor->getParameters() as $parameter) {
                $parameterType = $this->getParameterType($parameter);
                if ($parameterType) {
                    $parameterInjection = new ParameterInjection($parameter->name, $parameterType);
                } else {
                    $parameterInjection = new ParameterInjection($parameter->name);
                }
                $constructorInjection->addParameterInjection($parameterInjection);
            }
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
