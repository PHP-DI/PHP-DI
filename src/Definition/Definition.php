<?php

declare(strict_types=1);

namespace DI\Definition;

use DI\Factory\RequestedEntry;

/**
 * Definition.
 *
 * @internal This interface is internal to PHP-DI and may change between minor versions.
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
     * Definitions can be cast to string for debugging information.
     */
    public function __toString();
}
