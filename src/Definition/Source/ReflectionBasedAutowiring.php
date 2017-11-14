<?php

declare(strict_types=1);

namespace DI\Definition\Source;

use DI\Definition\ObjectDefinition;
use DI\Definition\ObjectDefinition\MethodInjection;
use DI\Definition\Reference;
use DI\Discovery\KnownClasses;

/**
 * Reads DI class definitions using reflection.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class ReflectionBasedAutowiring implements DefinitionSource, Autowiring
{
    /**
     * List of all classes that we know of in the application.
     *
     * @var KnownClasses|null
     */
    private $knownClasses;

    public function __construct(KnownClasses $knownClasses = null)
    {
        $this->knownClasses = $knownClasses;
    }

    public function autowire(string $name, ObjectDefinition $definition = null)
    {
        $className = $definition ? $definition->getClassName() : $name;

        if (!class_exists($className) && !interface_exists($className)) {
            return $definition;
        }

        $definition = $definition ?: new ObjectDefinition($name);

        // Constructor
        $class = new \ReflectionClass($className);
        $constructor = $class->getConstructor();
        if ($constructor && $constructor->isPublic()) {
            $constructorInjection = MethodInjection::constructor($this->getParametersDefinition($constructor));
            $definition->completeConstructorInjection($constructorInjection);
        }

        return $definition;
    }

    public function getDefinition(string $name)
    {
        return $this->autowire($name);
    }

    public function getDefinitions() : array
    {
        if (!$this->knownClasses) {
            return [];
        }

        return array_map([$this, 'getDefinition'], $this->knownClasses->getList());
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

            $parameterClass = $parameter->getClass();

            if ($parameterClass) {
                $parameters[$index] = new Reference($parameterClass->getName());
            }
        }

        return $parameters;
    }
}
