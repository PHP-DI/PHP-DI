<?php

declare(strict_types=1);

namespace DI\Invoker;

use DI\Factory\RequestedEntry;
use Invoker\ParameterResolver\ParameterResolver;
use Psr\Container\ContainerInterface;
use ReflectionFunctionAbstract;

/**
 * Inject the container, the definition or any other service using type-hints.
 *
 * {@internal This class is similar to TypeHintingResolver and TypeHintingContainerResolver,
 *            we use this instead for performance reasons}
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class BaseFactoryParameterResolver implements ParameterResolver
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getParameters(
        ReflectionFunctionAbstract $reflection,
        array $providedParameters,
        array $resolvedParameters
    ) : array {
        $parameters = $reflection->getParameters();

        // Skip parameters already resolved
        if (! empty($resolvedParameters)) {
            $parameters = array_diff_key($parameters, $resolvedParameters);
        }

        $parameterIndexes = array_keys($parameters);

        if ($parameterIndexes === [0]) {
            $resolvedParameters[0] = $this->container;
        } elseif (isset($parameters[0], $parameters[1])) {
            $resolvedParameters[0] = $this->container;
            $resolvedParameters[1] = $providedParameters[RequestedEntry::class];
        }

        return $resolvedParameters;
    }
}
