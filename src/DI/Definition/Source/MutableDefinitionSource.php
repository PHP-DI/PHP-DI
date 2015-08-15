<?php

namespace DI\Definition\Source;

use DI\Definition\Definition;

/**
 * Describes a definition source to which we can add new definitions.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
interface MutableDefinitionSource extends DefinitionSource
{
    /**
     * @param string     $entryName
     * @param Definition $definition
     * @return mixed
     */
    public function addDefinition($entryName, Definition $definition);
}
