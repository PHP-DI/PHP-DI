<?php

namespace DI\Invoker;

use Invoker\ParameterResolver\ParameterResolver;
use Interop\Container\ContainerInterface;
use DI\Definition\Definition;
use ReflectionFunctionAbstract;

/**
 * Inject container and definition entries if closure typehinted them
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
                if (ContainerInterface::class == $parameterClass->name && isset($providedParameters[0]) && $providedParameters[0] instanceof ContainerInterface) {
                    $resolvedParameters[$index] = $providedParameters[0];
                }
                if (Definition::class == $parameterClass->name && isset($providedParameters[1]) && $providedParameters[1] instanceof Definition) {
                    $resolvedParameters[$index] = $providedParameters[1];
                }
            }
        }

        return $resolvedParameters;
    }
}