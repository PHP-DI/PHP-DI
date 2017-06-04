<?php

declare(strict_types=1);

namespace DI\Definition;

/**
 * A definition that has a sub-definition.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
interface HasSubDefinition extends Definition
{
    public function getSubDefinitionName() : string;

    public function setSubDefinition(Definition $definition);
}
