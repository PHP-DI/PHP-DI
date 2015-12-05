<?php

namespace DI\Definition\Source;

use DI\Definition\Exception\DefinitionException;
use Interop\Container\Definition\DefinitionInterface;

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
     * @param string $name
     *
     * @throws DefinitionException An invalid definition was found.
     * @return DefinitionInterface|null
     */
    public function getDefinition($name);
}
