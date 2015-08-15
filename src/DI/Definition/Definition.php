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
     * Set the name of the entry in the container.
     *
     * @param string $name
     */
    public function setName($name);

    /**
     * Returns the name of the entry in the container.
     *
     * @deprecated Do not do anything anymore, will be removed.
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

    /**
     * Definitions can be cast to string for debugging information.
     *
     * This method is not enforced by the interface yet for backward
     * compatibility.
     */
    // public function __toString();
}
