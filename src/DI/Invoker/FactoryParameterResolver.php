<?php

namespace DI\Invoker;

use DI\Definition\Definition;
use Interop\Container\ContainerInterface;
use Invoker\ParameterResolver\ParameterResolver;
use ReflectionFunctionAbstract;

/**
 * Inject container and definition entries if closure typehinted them.
 *
 * @author Quim Calpe <quim@kalpe.com>
 */
class FactoryParameterResolver implements ParameterResolver
{
    public function getParameters(
        ReflectionFunctionAbstract $reflection,
        array $providedParameters,
        array $resolvedParameters
    ) {
        $parameters = $reflection->getParameters();

        foreach ($parameters as $index => $parameter) {
            $parameterClass = $parameter->getClass();

            if ($parameterClass) {
                if ('Interop\Container\ContainerInterface' == $parameterClass->name && isset($providedParameters[0]) && $providedParameters[0] instanceof ContainerInterface) {
                    $resolvedParameters[$index] = $providedParameters[0];
                }
                if ('DI\Definition\Definition' == $parameterClass->name && isset($providedParameters[1]) && $providedParameters[1] instanceof Definition) {
                    $resolvedParameters[$index] = $providedParameters[1];
                }
            }
        }

        return $resolvedParameters;
    }
}
