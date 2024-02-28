<?php

declare(strict_types=1);

namespace DI\Definition\Helper;

use DI\Definition\DefinitionInterface;

/**
 * Helps defining container entries.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
interface DefinitionHelperInterface
{
    /**
     * @param string $entryName Container entry name
     */
    public function getDefinition(string $entryName) : DefinitionInterface;
}
