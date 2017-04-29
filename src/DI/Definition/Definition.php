<?php

namespace DI\Definition;

use DI\Factory\RequestedEntry;

/**
 * Definition.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
interface Definition extends RequestedEntry
{
    /**
     * Returns the name of the entry in the container.
     */
    public function getName() : string;

    /**
     * Returns the scope of the entry.
     */
    public function getScope() : string;

     /**
      * Definitions can be cast to string for debugging information.
      */
     public function __toString();
}
