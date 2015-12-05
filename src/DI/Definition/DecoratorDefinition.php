<?php

namespace DI\Definition;

use Interop\Container\Definition\DefinitionInterface;

/**
 * Factory that decorates a sub-definition.
 *
 * @since 5.0
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class DecoratorDefinition extends FactoryDefinition implements Definition, HasSubDefinition
{
    /**
     * @var Definition
     */
    private $decorated;

    /**
     * @return string
     */
    public function getSubDefinitionName()
    {
        return $this->getName();
    }

    public function setSubDefinition(DefinitionInterface $definition)
    {
        $this->decorated = $definition;
    }

    /**
     * @return Definition
     */
    public function getDecoratedDefinition()
    {
        return $this->decorated;
    }
}
