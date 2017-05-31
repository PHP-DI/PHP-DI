<?php

namespace DI\Definition;

use DI\Scope;
use Psr\Container\ContainerInterface;

/**
 * Defines an alias from an entry to another.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class AliasDefinition implements Definition, SelfResolvingDefinition
{
    /**
     * Entry name.
     * @var string
     */
    private $name;

    /**
     * Name of the target entry.
     * @var string
     */
    private $targetEntryName;

    /**
     * @param string $name            Entry name
     * @param string $targetEntryName Name of the target entry
     */
    public function __construct($name, $targetEntryName)
    {
        $this->name = $name;
        $this->targetEntryName = $targetEntryName;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function getScope() : string
    {
        return Scope::PROTOTYPE;
    }

    public function getTargetEntryName() : string
    {
        return $this->targetEntryName;
    }

    public function resolve(ContainerInterface $container)
    {
        return $container->get($this->getTargetEntryName());
    }

    public function isResolvable(ContainerInterface $container) : bool
    {
        return $container->has($this->getTargetEntryName());
    }

    public function __toString()
    {
        return sprintf(
            'get(%s)',
            $this->targetEntryName
        );
    }
}
