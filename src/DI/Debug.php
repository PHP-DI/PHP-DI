<?php

namespace DI;

use DI\Definition\Definition;
use DI\Definition\Dumper\DefinitionDumperDispatcher;

/**
 * Debug utilities.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class Debug
{
    /**
     * Dump the definition to a string.
     *
     * @param Definition $definition
     *
     * @return string
     */
    public static function dumpDefinition(Definition $definition)
    {
        static $dumper;

        if (! $dumper) {
            $dumper = new DefinitionDumperDispatcher();
        }

        return $dumper->dump($definition);
    }
}
