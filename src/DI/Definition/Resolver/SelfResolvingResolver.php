<?php

namespace DI\Definition\Resolver;

use DI\Definition\Definition;
use DI\Definition\SelfResolvingDefinition;
use Interop\Container\ContainerInterface;

/**
 * Resolves a self-resolvable definition.
 *
 * @since 5.3
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class SelfResolvingResolver implements DefinitionResolver
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param SelfResolvingDefinition $definition
     *
     * {@inheritdoc}
     */
    public function resolve(Definition $definition, array $parameters = [])
    {
        return $definition->resolve($this->container);
    }

    /**
     * @param SelfResolvingDefinition $definition
     *
     * {@inheritdoc}
     */
    public function isResolvable(Definition $definition, array $parameters = [])
    {
        return $definition->isResolvable();
    }
}
