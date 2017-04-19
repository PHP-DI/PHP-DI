<?php

namespace DI\Definition;

use DI\Definition\Helper\DefinitionHelper;

/**
 * Represents a reference to a container entry.
 *
 * TODO should EntryReference and AliasDefinition be merged into a ReferenceDefinition?
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class EntryReference implements DefinitionHelper
{
    /**
     * Entry name.
     * @var string
     */
    private $name;

    /**
     * @param string $entryName Entry name
     */
    public function __construct(string $entryName)
    {
        $this->name = $entryName;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function getDefinition(string $entryName) : Definition
    {
        return new AliasDefinition($entryName, $this->name);
    }
}
