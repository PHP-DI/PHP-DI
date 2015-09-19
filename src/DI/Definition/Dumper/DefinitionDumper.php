<?php

namespace DI\Definition\Dumper;

use DI\Definition\Definition;

/**
 * Dumps definitions to help debugging.
 *
 * @since 4.1
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
interface DefinitionDumper
{
    /**
     * Returns the given definition as string representation.
     *
     * @param Definition $definition
     *
     * @return string
     */
    public function dump(Definition $definition);
}
