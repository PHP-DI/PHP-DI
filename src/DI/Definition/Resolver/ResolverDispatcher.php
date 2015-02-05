<?php

namespace DI\Definition\Resolver;

use DI\Definition\Definition;
use DI\Definition\Exception\DefinitionException;
use DI\Proxy\ProxyFactory;
use Interop\Container\ContainerInterface;

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
     * @var DefinitionResolver[]
     */
    private $definitionResolvers;

    public function __construct(array $definitionResolvers)
    {
        $this->definitionResolvers = $definitionResolvers;
    }

    /**
     * Creates a dispatcher with the default configuration.
     *
     * @param ContainerInterface $container
     * @param ProxyFactory       $proxyFactory
     * @return ResolverDispatcher
     */
    public static function createDefault(ContainerInterface $container, ProxyFactory $proxyFactory)
    {
        $arrayDefinitionResolver = new ArrayDefinitionResolver($container);

        $definitionResolvers = array(
            'DI\Definition\ValueDefinition'               => new ValueDefinitionResolver(),
            'DI\Definition\ArrayDefinition'               => $arrayDefinitionResolver,
            'DI\Definition\ArrayDefinitionExtension'      => $arrayDefinitionResolver,
            'DI\Definition\FactoryDefinition'             => new FactoryDefinitionResolver($container),
            'DI\Definition\AliasDefinition'               => new AliasDefinitionResolver($container),
            'DI\Definition\ClassDefinition'               => new ClassDefinitionResolver($container, $proxyFactory),
            'DI\Definition\InstanceDefinition'            => new InstanceDefinitionResolver($container, $proxyFactory),
            'DI\Definition\FunctionCallDefinition'        => new FunctionCallDefinitionResolver($container),
            'DI\Definition\EnvironmentVariableDefinition' => new EnvironmentVariableDefinitionResolver($container),
            'DI\Definition\StringDefinition'              => new StringDefinitionResolver($container),
        );

        return new self($definitionResolvers);
    }

    /**
     * Resolve a definition to a value.
     *
     * @param Definition $definition Object that defines how the value should be obtained.
     * @param array      $parameters Optional parameters to use to build the entry.
     *
     * @throws DefinitionException If the definition cannot be resolved.
     *
     * @return mixed Value obtained from the definition.
     */
    public function resolve(Definition $definition, array $parameters = array())
    {
        $definitionResolver = $this->getDefinitionResolver($definition);

        return $definitionResolver->resolve($definition, $parameters);
    }

    /**
     * Check if a definition can be resolved.
     *
     * @param Definition $definition Object that defines how the value should be obtained.
     * @param array      $parameters Optional parameters to use to build the entry.
     *
     * @return bool
     */
    public function isResolvable(Definition $definition, array $parameters = array())
    {
        $definitionResolver = $this->getDefinitionResolver($definition);

        return $definitionResolver->isResolvable($definition, $parameters);
    }

    /**
     * Returns a resolver capable of handling the given definition.
     *
     * @param Definition $definition
     *
     * @throws \RuntimeException No definition resolver was found for this type of definition.
     * @return DefinitionResolver
     */
    private function getDefinitionResolver(Definition $definition)
    {
        $definitionType = get_class($definition);

        if (! isset($this->definitionResolvers[$definitionType])) {
            throw new \RuntimeException("No definition resolver was configured for definition of type $definitionType");
        }

        return $this->definitionResolvers[$definitionType];
    }
}
