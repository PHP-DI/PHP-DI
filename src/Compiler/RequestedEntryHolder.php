<?php

declare(strict_types=1);

namespace DI\Compiler;

use DI\Factory\RequestedEntryInterface;

/**
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class RequestedEntryHolder implements RequestedEntryInterface
{
    public function __construct(
        private string $name,
    ) {
    }

    public function getName() : string
    {
        return $this->name;
    }
}
