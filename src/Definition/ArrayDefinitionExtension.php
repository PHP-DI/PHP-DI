<?php

declare(strict_types=1);

namespace DI\Definition;

use DI\Definition\Exception\InvalidDefinition;

/**
 * Extends an array definition by adding new elements into it.
 *
 * @since 5.0
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class ArrayDefinitionExtension extends ArrayDefinition implements ExtendsPreviousDefinitionInterface
{
    private ?ArrayDefinition $subDefinition = null;

    public function getValues() : array
    {
        if (! $this->subDefinition) {
            return parent::getValues();
        }

        return array_merge($this->subDefinition->getValues(), parent::getValues());
    }

    public function setExtendedDefinition(DefinitionInterface $definition) : void
    {
        if (! $definition instanceof ArrayDefinition) {
            throw new InvalidDefinition(sprintf(
                'Definition %s tries to add array entries but the previous definition is not an array',
                $this->getName()
            ));
        }

        $this->subDefinition = $definition;
    }
}
