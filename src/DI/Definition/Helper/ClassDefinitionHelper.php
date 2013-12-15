<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.io/PHP-DI/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Definition\Helper;

use DI\Definition\ClassDefinition;
use DI\Definition\ClassInjection\MethodInjection;
use DI\Definition\ClassInjection\PropertyInjection;
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

    public function __construct($className = null)
    {
        $this->className = $className;
    }

    public function lazy()
    {
        $this->lazy = true;
        return $this;
    }

    public function withScope(Scope $scope)
    {
        $this->scope = $scope;
        return $this;
    }

    public function withConstructor()
    {
        $this->constructor = func_get_args();
        return $this;
    }

    public function withProperty($property, $value)
    {
        $this->properties[$property] = $value;
        return $this;
    }

    public function withMethod($method)
    {
        $args = func_get_args();
        array_shift($args);
        $this->methods[$method] = $args;
        return $this;
    }

    public function withMethodParameter($method, $parameter, $value)
    {
        if (! isset($this->methods[$method])) {
            $this->methods[$method] = array();
        }
        $this->methods[$method][$parameter] = $value;
        return $this;
    }

    /**
     * @param string $entryName Container entry name
     * @return ClassDefinition
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
            $definition->setConstructorInjection(new MethodInjection('__construct', $parameters));
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
                $definition->addMethodInjection(new MethodInjection($method, $parameters));
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
