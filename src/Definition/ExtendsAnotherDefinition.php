<?php

declare(strict_types=1);

namespace DI\Definition;

/**
 * A definition that extends another definition.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
interface ExtendsAnotherDefinition extends Definition
{
    public function getExtendedDefinitionName() : string;

    public function setExtendedDefinition(Definition $definition);
}
