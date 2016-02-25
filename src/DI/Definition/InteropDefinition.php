<?php

namespace DI\Definition;

use DI\Scope;

/**
 * Container entry returned by a container-interop service provider.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class InteropDefinition implements Definition, HasSubDefinition
{
    /**
     * Entry name.
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $class;

    /**
     * @var string
     */
    private $method;

    /**
     * @var Definition|null
     */
    private $previousDefinition;

    public function __construct($name, $class, $method)
    {
        $this->name = $name;
        $this->class = $class;
        $this->method = $method;
    }

    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    public function getScope()
    {
        return Scope::SINGLETON;
    }

    public function getSubDefinitionName()
    {
        return $this->name;
    }

    public function setSubDefinition(Definition $definition)
    {
        $this->previousDefinition = $definition;
    }

    /**
     * @return Definition|null
     */
    public function getPreviousDefinition()
    {
        return $this->previousDefinition;
    }
}
