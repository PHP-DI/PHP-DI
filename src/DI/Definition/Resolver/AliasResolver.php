<?php

namespace DI\Definition\Resolver;

use Interop\Container\ContainerInterface;
use Interop\Container\Definition\DefinitionInterface;
use Interop\Container\Definition\ReferenceDefinitionInterface;

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
     * @param ReferenceDefinitionInterface $definition
     *
     * {@inheritdoc}
     */
    public function resolve(DefinitionInterface $definition, array $parameters = [])
    {
        return $this->container->get($definition->getTarget());
    }

    /**
     * @param ReferenceDefinitionInterface $definition
     *
     * {@inheritdoc}
     */
    public function isResolvable(DefinitionInterface $definition, array $parameters = [])
    {
        return $this->container->has($definition->getTarget());
    }
}
