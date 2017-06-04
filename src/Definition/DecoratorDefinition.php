<?php

declare(strict_types=1);

namespace DI\Definition;

/**
 * Factory that decorates a sub-definition.
 *
 * @since 5.0
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class DecoratorDefinition extends FactoryDefinition implements Definition, HasSubDefinition
{
    /**
     * @var Definition|null
     */
    private $decorated;

    public function getSubDefinitionName() : string
    {
        return $this->getName();
    }

    public function setSubDefinition(Definition $definition)
    {
        $this->decorated = $definition;
    }

    /**
     * @return Definition|null
     */
    public function getDecoratedDefinition()
    {
        return $this->decorated;
    }

    public function __toString()
    {
        return 'Decorate(' . $this->getSubDefinitionName() . ')';
    }
}
