<?php

namespace DI\Definition\Source;

use DI\Definition\AutowireDefinition;
use DI\Definition\Exception\InvalidDefinition;

/**
 * Source of definitions for entries of the container.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
interface Autowiring
{
    /**
     * Autowire the given definition.
     *
     * @throws InvalidDefinition An invalid definition was found.
     * @return AutowireDefinition|null
     */
    public function autowire(string $name, AutowireDefinition $definition = null);
}
