<?php

namespace DI\Definition;

use Interop\Container\Definition\DefinitionInterface;

/**
 * A definition that has a sub-definition.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
interface HasSubDefinition extends Definition
{
    /**
     * @return string
     */
    public function getSubDefinitionName();

    public function setSubDefinition(DefinitionInterface $definition);
}
