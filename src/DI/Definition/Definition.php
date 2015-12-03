<?php

namespace DI\Definition;

use DI\Factory\RequestedEntry;
use Interop\Container\Definition\DefinitionInterface;

/**
 * Definition.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
interface Definition extends DefinitionInterface, RequestedEntry
{
    /**
     * Returns the name of the entry in the container.
     *
     * @return string
     */
    public function getName();

    /**
     * Returns the scope of the entry.
     *
     * @return string
     */
    public function getScope();
}
