<?php

namespace DI\Definition\Resolver;

use DI\Definition\Definition;
use DI\Definition\Exception\DefinitionException;
use DI\Definition\FactoryDefinition;
use Interop\Container\ContainerInterface;
use Invoker\Exception\NotCallableException;
use Invoker\Invoker;
use Invoker\ParameterResolver\NumericArrayResolver;

/**
 * Resolves a factory definition to a value.
 *
 * @since 4.0
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class FactoryResolver implements DefinitionResolver
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var Invoker|null
     */
    private $invoker;

    /**
     * The resolver needs a container. This container will be passed to the factory as a parameter
     * so that the factory can access other entries of the container.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Resolve a factory definition to a value.
     *
     * This will call the callable of the definition.
     *
     * @param FactoryDefinition $definition
     *
     * {@inheritdoc}
     */
    public function resolve(Definition $definition, array $parameters = [])
    {
        if (! $this->invoker) {
            $this->invoker = new Invoker(new NumericArrayResolver, $this->container);
        }

        try {
            return $this->invoker->call($definition->getCallable(), [$this->container]);
        } catch (NotCallableException $e) {
            throw new DefinitionException(sprintf(
                'Entry "%s" cannot be resolved: factory %s',
                $definition->getName(),
                $e->getMessage()
            ));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isResolvable(Definition $definition, array $parameters = [])
    {
        return true;
    }
}
