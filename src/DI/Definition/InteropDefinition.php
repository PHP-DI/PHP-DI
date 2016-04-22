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
    private $registryKey;

    /**
     * @var Definition|null
     */
    private $previousDefinition;

    public function __construct($name, $registryKey)
    {
        $this->name = $name;
        $this->registryKey = $registryKey;
    }

    public function getName()
    {
        return $this->name;
    }

    /**
     * @return int
     */
    public function getRegistryKey()
    {
        return $this->registryKey;
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
