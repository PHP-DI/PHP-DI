<?php

namespace DI\Definition\Resolver;

use DI\Proxy\ProxyFactory;
use Interop\Container\ContainerInterface;
use Interop\Container\Definition\DefinitionInterface;
use Interop\Container\Definition\FactoryCallDefinitionInterface;
use Interop\Container\Definition\ObjectDefinitionInterface;
use Interop\Container\Definition\ParameterDefinitionInterface;
use Interop\Container\Definition\ReferenceDefinitionInterface;

/**
 * Dispatches to more specific resolvers.
 *
 * Dynamic dispatch pattern.
 *
 * @since 5.0
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class ResolverDispatcher implements DefinitionResolver
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var ProxyFactory
     */
    private $proxyFactory;

    private $valueResolver;
    private $arrayResolver;
    private $factoryResolver;
    private $decoratorResolver;
    private $aliasResolver;
    private $objectResolver;
    private $instanceResolver;
    private $envVariableResolver;
    private $stringResolver;
    private $definitionInteropResolver;

    public function __construct(ContainerInterface $container, ProxyFactory $proxyFactory)
    {
        $this->container = $container;
        $this->proxyFactory = $proxyFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(DefinitionInterface $definition, array $parameters = [])
    {
        $definitionResolver = $this->getDefinitionResolver($definition);

        return $definitionResolver->resolve($definition, $parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function isResolvable(DefinitionInterface $definition, array $parameters = [])
    {
        $definitionResolver = $this->getDefinitionResolver($definition);

        return $definitionResolver->isResolvable($definition, $parameters);
    }

    /**
     * Returns a resolver capable of handling the given definition.
     *
     * @param DefinitionInterface $definition
     *
     * @throws \RuntimeException No definition resolver was found for this type of definition.
     * @return DefinitionResolver
     */
    private function getDefinitionResolver(DefinitionInterface $definition)
    {
        switch (true) {
            case ($definition instanceof \DI\Definition\ObjectDefinition):
                if (! $this->objectResolver) {
                    $this->objectResolver = new ObjectCreator($this, $this->proxyFactory);
                }

                return $this->objectResolver;
            case ($definition instanceof ParameterDefinitionInterface):
                if (! $this->valueResolver) {
                    $this->valueResolver = new ValueResolver();
                }

                return $this->valueResolver;
            case ($definition instanceof ReferenceDefinitionInterface):
                if (! $this->aliasResolver) {
                    $this->aliasResolver = new AliasResolver($this->container);
                }

                return $this->aliasResolver;
            case ($definition instanceof \DI\Definition\DecoratorDefinition):
                if (! $this->decoratorResolver) {
                    $this->decoratorResolver = new DecoratorResolver($this->container, $this);
                }

                return $this->decoratorResolver;
            case ($definition instanceof \DI\Definition\FactoryDefinition):
                if (! $this->factoryResolver) {
                    $this->factoryResolver = new FactoryResolver($this->container);
                }

                return $this->factoryResolver;
            case ($definition instanceof \DI\Definition\ArrayDefinition):
                if (! $this->arrayResolver) {
                    $this->arrayResolver = new ArrayResolver($this);
                }

                return $this->arrayResolver;
            case ($definition instanceof \DI\Definition\EnvironmentVariableDefinition):
                if (! $this->envVariableResolver) {
                    $this->envVariableResolver = new EnvironmentVariableResolver($this);
                }

                return $this->envVariableResolver;
            case ($definition instanceof \DI\Definition\StringDefinition):
                if (! $this->stringResolver) {
                    $this->stringResolver = new StringResolver($this->container);
                }

                return $this->stringResolver;
            case ($definition instanceof \DI\Definition\InstanceDefinition):
                if (! $this->instanceResolver) {
                    $this->instanceResolver = new InstanceInjector($this, $this->proxyFactory);
                }

                return $this->instanceResolver;
            case ($definition instanceof ObjectDefinitionInterface):
                if (! $this->definitionInteropResolver) {
                    $this->definitionInteropResolver = new \Assembly\Container\DefinitionResolver($this->container);
                }

                return $this->definitionInteropResolver;
            case ($definition instanceof FactoryCallDefinitionInterface):
                if (! $this->definitionInteropResolver) {
                    $this->definitionInteropResolver = new \Assembly\Container\DefinitionResolver($this->container);
                }

                return $this->definitionInteropResolver;
            default:
                throw new \RuntimeException('No definition resolver was configured for definition of type ' . get_class($definition));
        }
    }
}
