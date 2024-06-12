<?php

declare(strict_types=1);

namespace DI\Definition\Source;

use DI\Definition\ObjectDefinition;
use DI\Definition\ObjectDefinition\MethodInjection;
use DI\Definition\Reference;
use ReflectionClass;
use ReflectionNamedType;

/**
 * Reads DI class definitions using reflection.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class ReflectionBasedAutowiring implements DefinitionSource, Autowiring
{
    public function autowire(string $name, ObjectDefinition $definition = null) : ObjectDefinition|null
    {
        $className = $definition ? $definition->getClassName() : $name;

        if (!class_exists($className) && !interface_exists($className)) {
            return $definition;
        }

        $definition = $definition ?: new ObjectDefinition($name);

        $class = new ReflectionClass($className);

        // Constructor
        $constructor = $class->getConstructor();
        if ($constructor && $constructor->isPublic()) {
            $constructorInjection = MethodInjection::constructor($this->getParametersDefinition($constructor));
            $definition->completeConstructorInjection($constructorInjection);
        }

        // Ensure method injections are complete
        $methodInjections = $definition->getMethodInjections();
        foreach ($methodInjections as $methodInjection) {
            $reflectionMethodInjection = new MethodInjection(
                $methodInjection->getMethodName(),
                $this->getParametersDefinition($class->getMethod($methodInjection->getMethodName())),
            );
            $definition->completeFirstMethodInjection($reflectionMethodInjection);
        }

        return $definition;
    }

    public function getDefinition(string $name) : ObjectDefinition|null
    {
        return $this->autowire($name);
    }

    /**
     * Autowiring cannot guess all existing definitions.
     */
    public function getDefinitions() : array
    {
        return [];
    }

    /**
     * Read the type-hinting from the parameters of the function.
     */
    private function getParametersDefinition(\ReflectionFunctionAbstract $constructor) : array
    {
        $parameters = [];

        foreach ($constructor->getParameters() as $index => $parameter) {
            // Skip optional parameters
            if ($parameter->isOptional()) {
                continue;
            }

            $parameterType = $parameter->getType();
            if (!$parameterType) {
                // No type
                continue;
            }
            if (!$parameterType instanceof ReflectionNamedType) {
                // Union types are not supported
                continue;
            }
            if ($parameterType->isBuiltin()) {
                // Primitive types are not supported
                continue;
            }

            $parameters[$index] = new Reference($parameterType->getName());
        }

        return $parameters;
    }
}
