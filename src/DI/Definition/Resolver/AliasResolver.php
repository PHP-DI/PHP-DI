<?php

namespace DI\Definition\Resolver;

use DI\Definition\AliasDefinition;
use DI\Definition\Definition;
use Interop\Container\ContainerInterface;

/**
 * Resolves an alias definition to a value.
 *
 * @since 4.0
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class AliasResolver implements DefinitionResolver
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * The resolver needs a container.
     * This container will be used to get the entry to which the alias points to.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Resolve an alias definition to a value.
     *
     * This will return the entry the alias points to.
     *
     * @param AliasDefinition $definition
     *
     * {@inheritdoc}
     */
    public function resolve(Definition $definition, array $parameters = [])
    {
        return $this->container->get($definition->getTargetEntryName());
    }

    /**
     * @param AliasDefinition $definition
     *
     * {@inheritdoc}
     */
    public function isResolvable(Definition $definition, array $parameters = [])
    {
        return $this->container->has($definition->getTargetEntryName());
    }
}
