<?php

namespace DI\Definition\Source;

use DI\Definition\AutowireDefinition;
use DI\Definition\Exception\DefinitionException;

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
     * @return AutowireDefinition|null
     *
     * @throws DefinitionException An invalid definition was found.
     */
    public function autowire(string $name, AutowireDefinition $definition = null);
}
