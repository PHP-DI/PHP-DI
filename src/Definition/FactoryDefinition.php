<?php

declare(strict_types=1);

namespace DI\Definition;

/**
 * Definition of a value or class with a factory.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class FactoryDefinition implements DefinitionInterface
{
    /**
     * Entry name.
     */
    private string $name;

    /**
     * Callable that returns the value.
     * @var callable
     */
    private $factory;

    /**
     * Factory parameters.
     * @var mixed[]
     */
    private array $parameters;

    /**
     * @param string $name Entry name
     * @param callable|array|string $factory Callable that returns the value associated to the entry name.
     * @param array $parameters Parameters to be passed to the callable
     */
    public function __construct(string $name, callable|array|string $factory, array $parameters = [])
    {
        $this->name = $name;
        $this->factory = $factory;
        $this->parameters = $parameters;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function setName(string $name) : void
    {
        $this->name = $name;
    }

    /**
     * @return callable|array|string Callable that returns the value associated to the entry name.
     */
    public function getCallable() : callable|array|string
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

    public function replaceNestedDefinitions(callable $replacer) : void
    {
        $this->parameters = array_map($replacer, $this->parameters);
    }

    public function __toString() : string
    {
        return 'Factory';
    }
}
