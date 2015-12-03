<?php

namespace DI\Definition;

use DI\Scope;
use Interop\Container\Definition\ReferenceDefinitionInterface;

/**
 * Defines an alias from an entry to another.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class AliasDefinition implements CacheableDefinition, ReferenceDefinitionInterface
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
    private $target;

    /**
     * @param string $name            Entry name
     * @param string $targetEntryName Name of the target entry
     */
    public function __construct($name, $targetEntryName)
    {
        $this->name = $name;
        $this->target = $targetEntryName;
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
     * {@inheritdoc}
     */
    public function getTarget()
    {
        return $this->target;
    }
}
