<?php

declare(strict_types=1);

namespace DI\Definition;

/**
 * A definition that extends a previous definition with the same name.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
interface ExtendsPreviousDefinitionInterface extends DefinitionInterface
{
    public function setExtendedDefinition(DefinitionInterface $definition) : void;
}
