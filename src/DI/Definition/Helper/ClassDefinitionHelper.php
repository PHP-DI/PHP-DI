<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Definition\Helper;

use DI\Definition\ClassDefinition;
use DI\Definition\ClassDefinition\MethodInjection;
use DI\Definition\ClassDefinition\PropertyInjection;
use DI\Scope;

/**
 * Helps defining how to create an instance of a class.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class ClassDefinitionHelper implements DefinitionHelper
{
    /**
     * @var string|null
     */
    private $className;

    /**
     * @var boolean|null
     */
    private $lazy;

    /**
     * @var Scope|null
     */
    private $scope;

    /**
     * Array of constructor parameters.
     * @var array
     */
    private $constructor = array();

    /**
     * Array of properties and their value.
     * @var array
     */
    private $properties = array();

    /**
     * Array of methods and their parameters.
     * @var array
     */
    private $methods = array();

    /**
     * Helper for defining an object.
     *
     * @param string|null $className Class name of the object.
     *                               If null, the name of the entry (in the container) will be used as class name.
     */
    public function __construct($className = null)
    {
        $this->className = $className;
    }

    /**
     * Define the entry as lazy.
     *
     * A lazy entry is created only when it is used, a proxy is injected instead.
     *
     * @return ClassDefinitionHelper
     */
    public function lazy()
    {
        $this->lazy = true;
        return $this;
    }

    /**
     * Defines the scope of the entry.
     *
     * @param Scope $scope
     *
     * @return ClassDefinitionHelper
     */
    public function scope(Scope $scope)
    {
        $this->scope = $scope;
        return $this;
    }

    /**
     * Defines the arguments to use to call the constructor.
     *
     * This method takes a variable number of arguments, example:
     *     ->constructor($param1, $param2, $param3)
     *
     * @param mixed ... Parameters to use for calling the constructor of the class.
     *
     * @return ClassDefinitionHelper
     */
    public function constructor()
    {
        $this->constructor = func_get_args();
        return $this;
    }

    /**
     * Defines a value for a specific argument of the constructor.
     *
     * This method is usually used together with annotations or autowiring, when a parameter
     * is not (or cannot be) type-hinted. Using this method instead of constructor() allows to
     * avoid defining all the parameters (letting them being resolved using annotations or autowiring)
     * and only define one.
     *
     * @param string $parameter Parameter for which the value will be given.
     * @param mixed  $value     Value to give to this parameter.
     *
     * @return ClassDefinitionHelper
     */
    public function constructorParameter($parameter, $value)
    {
        $this->constructor[$parameter] = $value;
        return $this;
    }

    /**
     * Defines a value to inject in a property of the object.
     *
     * @param string $property Entry in which to inject the value.
     * @param mixed  $value    Value to inject in the property.
     *
     * @return ClassDefinitionHelper
     */
    public function property($property, $value)
    {
        $this->properties[$property] = $value;
        return $this;
    }

    /**
     * Defines a method to call and the arguments to use.
     *
     * This method takes a variable number of arguments after the method name, example:
     *     ->method('myMethod', $param1, $param2)
     *
     * @param string $method Name of the method to call.
     * @param mixed  ...     Parameters to use for calling the method.
     *
     * @return ClassDefinitionHelper
     */
    public function method($method)
    {
        $args = func_get_args();
        array_shift($args);
        $this->methods[$method] = $args;
        return $this;
    }

    /**
     * Defines a method to call and a value for a specific argument.
     *
     * This method is usually used together with annotations or autowiring, when a parameter
     * is not (or cannot be) type-hinted. Using this method instead of method() allows to
     * avoid defining all the parameters (letting them being resolved using annotations or autowiring)
     * and only define one.
     *
     * @param string $method    Name of the method to call.
     * @param string $parameter Parameter for which the value will be given.
     * @param mixed  $value     Value to give to this parameter.
     *
     * @return ClassDefinitionHelper
     */
    public function methodParameter($method, $parameter, $value)
    {
        // Special case for the constructor
        if ($method === '__construct') {
            $this->constructor[$parameter] = $value;
            return $this;
        }

        if (! isset($this->methods[$method])) {
            $this->methods[$method] = array();
        }
        $this->methods[$method][$parameter] = $value;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefinition($entryName)
    {
        $definition = new ClassDefinition($entryName, $this->className);

        if ($this->lazy !== null) {
            $definition->setLazy($this->lazy);
        }
        if ($this->scope !== null) {
            $definition->setScope($this->scope);
        }

        if (! empty($this->constructor)) {
            $parameters = $this->fixParameters($definition, '__construct', $this->constructor);
            $constructorInjection = MethodInjection::constructor($parameters);
            $definition->setConstructorInjection($constructorInjection);
        }

        if (! empty($this->properties)) {
            foreach ($this->properties as $property => $value) {
                $definition->addPropertyInjection(
                    new PropertyInjection($property, $value)
                );
            }
        }

        if (! empty($this->methods)) {
            foreach ($this->methods as $method => $parameters) {
                $parameters = $this->fixParameters($definition, $method, $parameters);
                $methodInjection = new MethodInjection($method, $parameters);
                $definition->addMethodInjection($methodInjection);
            }
        }

        return $definition;
    }

    /**
     * Fixes parameters indexed by the parameter name -> reindex by position.
     *
     * This is necessary so that merging definitions between sources is possible.
     *
     * @param ClassDefinition $definition
     * @param string          $method
     * @param array           $parameters
     * @return array
     */
    private function fixParameters(ClassDefinition $definition, $method, $parameters)
    {
        $fixedParameters = array();

        foreach ($parameters as $index => $parameter) {
            // Parameter indexed by the parameter name, we reindex it with its position
            if (is_string($index)) {
                $callable = array($definition->getClassName(), $method);
                $reflectionParameter = new \ReflectionParameter($callable, $index);

                $index = $reflectionParameter->getPosition();
            }

            $fixedParameters[$index] = $parameter;
        }

        return $fixedParameters;
    }
}
