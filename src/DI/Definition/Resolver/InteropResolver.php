<?php

namespace DI\Definition\Resolver;

use DI\Definition\Definition;
use DI\Definition\Exception\DefinitionException;
use DI\Definition\InteropDefinition;
use Interop\Container\ContainerInterface;

/**
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class InteropResolver implements DefinitionResolver
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var DefinitionResolver
     */
    private $definitionResolver;

    /**
     * @param DefinitionResolver $definitionResolver Used to resolve previous definitions.
     */
    public function __construct(ContainerInterface $container, DefinitionResolver $definitionResolver)
    {
        $this->container = $container;
        $this->definitionResolver = $definitionResolver;
    }

    /**
     * @param InteropDefinition $definition
     *
     * {@inheritdoc}
     */
    public function resolve(Definition $definition, array $parameters = [])
    {
        $class = $definition->getClass();
        $method = $definition->getMethod();
        $previousDefinition = $definition->getPreviousDefinition();

        if ($previousDefinition instanceof Definition) {
            try {
                $previous = $this->definitionResolver->resolve($previousDefinition);
            } catch (DefinitionException $e) {
                $previous = null;
            }
        } else {
            $previous = null;
        }

        return call_user_func([$class, $method], $this->container, $previous);
    }

    public function isResolvable(Definition $definition, array $parameters = [])
    {
        return true;
    }
}
