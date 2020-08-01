<?php

declare(strict_types=1);

namespace DI\Definition;

use Psr\Container\ContainerInterface;

/**
 * Represents a reference to another entry.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class Reference implements Definition, SelfResolvingDefinition
{
    /**
     * Entry name.
     */
    private string $name = '';

    /**
     * Name of the target entry.
     */
    private string $targetEntryName;

    /**
     * @param string $targetEntryName Name of the target entry
     */
    public function __construct(string $targetEntryName)
    {
        $this->targetEntryName = $targetEntryName;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function setName(string $name) : void
    {
        $this->name = $name;
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

    public function replaceNestedDefinitions(callable $replacer) : void
    {
        // no nested definitions
    }

    public function __toString()
    {
        return sprintf(
            'get(%s)',
            $this->targetEntryName
        );
    }
}
