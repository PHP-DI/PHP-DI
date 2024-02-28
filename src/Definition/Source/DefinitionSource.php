<?php

declare(strict_types=1);

namespace DI\Definition\Source;

use DI\Definition\DefinitionInterface;
use DI\Definition\Exception\InvalidDefinition;

/**
 * Source of definitions for entries of the container.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
interface DefinitionSource
{
    /**
     * Returns the DI definition for the entry name.
     *
     * @throws InvalidDefinition An invalid definition was found.
     */
    public function getDefinition(string $name) : DefinitionInterface|null;

    /**
     * @return array<string,DefinitionInterface> Definitions indexed by their name.
     */
    public function getDefinitions() : array;
}
