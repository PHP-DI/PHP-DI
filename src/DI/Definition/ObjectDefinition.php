<?php

namespace DI\Definition;

use DI\Definition\Dumper\ObjectDefinitionDumper;
use DI\Definition\ObjectDefinition\MethodInjection;
use DI\Definition\ObjectDefinition\PropertyInjection;
use DI\Scope;
use ReflectionClass;

/**
 * Defines how an object can be instantiated.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class ObjectDefinition implements Definition, CacheableDefinition
{
    /**
     * Entry name (most of the time, same as $classname).
     * @var string
     */
    private $name;

    /**
     * Class name (if null, then the class name is $name).
     * @var string|null
     */
    protected $className;

    /**
     * Constructor parameter injection.
     * @var MethodInjection|null
     */
    protected $constructorInjection;

    /**
     * Property injections.
     * @var PropertyInjection[]
     */
    protected $propertyInjections = [];

    /**
     * Method calls.
     * @var MethodInjection[][]
     */
    protected $methodInjections = [];

    /**
     * @var string|null
     */
    protected $scope;

    /**
     * @var bool|null
     */
    protected $lazy;

    /**
     * Store if the class exists. Storing it (in cache) avoids recomputing this.
     *
     * @var bool
     */
    private $classExists;

    /**
     * Store if the class is instantiable. Storing it (in cache) avoids recomputing this.
     *
     * @var bool
     */
    private $isInstantiable;

    /**
     * @param string $name Class name
     * @param string $className
     */
    public function __construct($name, $className = null)
    {
        $this->name = (string) $name;
        $this->setClassName($className);
    }

    /**
     * @return string Entry name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string|null $className
     */
    public function setClassName($className)
    {
        $this->className = $className;

        $this->updateCache();
    }

    /**
     * @return string Class name
     */
    public function getClassName()
    {
        if ($this->className !== null) {
            return $this->className;
        }

        return $this->name;
    }

    /**
     * @return MethodInjection|null
     */
    public function getConstructorInjection()
    {
        return $this->constructorInjection;
    }

    /**
     * @param MethodInjection $constructorInjection
     */
    public function setConstructorInjection(MethodInjection $constructorInjection)
    {
        $this->constructorInjection = $constructorInjection;
    }

    /**
     * @return PropertyInjection[] Property injections
     */
    public function getPropertyInjections()
    {
        return $this->propertyInjections;
    }

    public function addPropertyInjection(PropertyInjection $propertyInjection)
    {
        $className = $propertyInjection->getClassName();
        if ($className) {
            // Index with the class name to avoid collisions between parent and
            // child private properties with the same name
            $key = $className . '::' . $propertyInjection->getPropertyName();
        } else {
            $key = $propertyInjection->getPropertyName();
        }

        $this->propertyInjections[$key] = $propertyInjection;
    }

    /**
     * @return MethodInjection[] Method injections
     */
    public function getMethodInjections()
    {
        // Return array leafs
        $injections = [];
        array_walk_recursive($this->methodInjections, function ($injection) use (&$injections) {
            $injections[] = $injection;
        });

        return $injections;
    }

    /**
     * @param MethodInjection $methodInjection
     */
    public function addMethodInjection(MethodInjection $methodInjection)
    {
        $method = $methodInjection->getMethodName();
        if (! isset($this->methodInjections[$method])) {
            $this->methodInjections[$method] = [];
        }
        $this->methodInjections[$method][] = $methodInjection;
    }

    /**
     * @param string $scope
     */
    public function setScope($scope)
    {
        $this->scope = $scope;
    }

    /**
     * {@inheritdoc}
     */
    public function getScope()
    {
        return $this->scope ?: Scope::SINGLETON;
    }

    /**
     * @param bool|null $lazy
     */
    public function setLazy($lazy)
    {
        $this->lazy = $lazy;
    }

    /**
     * @return bool
     */
    public function isLazy()
    {
        if ($this->lazy !== null) {
            return $this->lazy;
        } else {
            // Default value
            return false;
        }
    }

    /**
     * @return bool
     */
    public function classExists()
    {
        return $this->classExists;
    }

    /**
     * @return bool
     */
    public function isInstantiable()
    {
        return $this->isInstantiable;
    }

    public function __toString()
    {
        return (new ObjectDefinitionDumper)->dump($this);
    }

    private function updateCache()
    {
        $className = $this->getClassName();

        $this->classExists = class_exists($className) || interface_exists($className);

        if (! $this->classExists) {
            $this->isInstantiable = false;

            return;
        }

        $class = new ReflectionClass($className);
        $this->isInstantiable = $class->isInstantiable();
    }
}
