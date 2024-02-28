<?php

declare(strict_types=1);

namespace DI\Definition\Source;

use DI\Definition\DefinitionInterface;

/**
 * Describes a definition source to which we can add new definitions.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
interface MutableDefinitionSource extends DefinitionSource
{
    public function addDefinition(DefinitionInterface $definition) : void;
}
