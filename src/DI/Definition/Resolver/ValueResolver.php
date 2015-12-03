<?php

namespace DI\Definition\Resolver;

use DI\Definition\ValueDefinition;
use Interop\Container\Definition\DefinitionInterface;

/**
 * Resolves a value definition to a value.
 *
 * @since 4.0
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class ValueResolver implements DefinitionResolver
{
    /**
     * Resolve a value definition to a value.
     *
     * A value definition is simple, so this will just return the value of the ValueDefinition.
     *
     * @param ValueDefinition $definition
     *
     * {@inheritdoc}
     */
    public function resolve(DefinitionInterface $definition, array $parameters = [])
    {
        return $definition->getValue();
    }

    /**
     * {@inheritdoc}
     */
    public function isResolvable(DefinitionInterface $definition, array $parameters = [])
    {
        return true;
    }
}
