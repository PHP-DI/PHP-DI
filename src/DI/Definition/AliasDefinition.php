<?php

namespace DI\Definition;

use DI\Scope;
use Interop\Container\ContainerInterface;

/**
 * Defines an alias from an entry to another.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class AliasDefinition implements CacheableDefinition, SelfResolvingDefinition
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
     * @param string $targetEntryName Name of the target entry
     */
    public function __construct($targetEntryName)
    {
        $this->targetEntryName = $targetEntryName;
    }

    /**
     * {@inheritdoc}
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getScope()
    {
        return Scope::PROTOTYPE;
    }

    /**
     * @return string
     */
    public function getTargetEntryName()
    {
        return $this->targetEntryName;
    }

    public function resolve(ContainerInterface $container)
    {
        return $container->get($this->getTargetEntryName());
    }

    public function isResolvable(ContainerInterface $container)
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
