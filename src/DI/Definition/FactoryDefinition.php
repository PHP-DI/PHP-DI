<?php

namespace DI\Definition;

use DI\Scope;

/**
 * Definition of a value or class with a factory.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class FactoryDefinition implements Definition
{
    /**
     * Entry name.
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $scope;

    /**
     * Callable that returns the value.
     * @var callable
     */
    private $factory;

    /**
     * Factory parameters.
     * @var array
     */
    private $parameters = [];

    /**
     * @param string $name Entry name
     * @param callable $factory Callable that returns the value associated to the entry name.
     * @param array $parameters Parameters to be passed to the callable
     */
    public function __construct(string $name, $factory, string $scope = null, array $parameters = [])
    {
        $this->name = $name;
        $this->factory = $factory;
        $this->scope = $scope;
        $this->parameters = $parameters;
    }

    public function getName() : string
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     * Default scope is singleton: the callable is called once and the result is shared.
     */
    public function getScope() : string
    {
        return $this->scope ?: Scope::SINGLETON;
    }

    /**
     * @return callable Callable that returns the value associated to the entry name.
     */
    public function getCallable()
    {
        return $this->factory;
    }

    /**
     * @return array Array containing the parameters to be passed to the callable, indexed by name.
     */
    public function getParameters() : array
    {
        return $this->parameters;
    }

    public function __toString()
    {
        return 'Factory';
    }
}
