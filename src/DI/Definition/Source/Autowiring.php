<?php

namespace DI\Definition\Source;

use DI\Definition\EntryReference;
use DI\Definition\ObjectDefinition;
use DI\Definition\ObjectDefinition\MethodInjection;

/**
 * Reads DI class definitions using reflection.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class Autowiring implements DefinitionSource
{

    /** @var string */
    private $scalarEntryFormat;

    public function __construct($scalarEntryFormat = 'className::parameter')
    {
        $this->scalarEntryFormat = $scalarEntryFormat;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefinition($name)
    {
        if (!class_exists($name) && !interface_exists($name)) {
            return null;
        }

        $definition = new ObjectDefinition($name);

        // Constructor
        $class = new \ReflectionClass($name);
        $constructor = $class->getConstructor();
        if ($constructor && $constructor->isPublic()) {
            $definition->setConstructorInjection(
                MethodInjection::constructor($this->getParametersDefinition($name, $constructor))
            );
        }

        return $definition;
    }

    /**
     * Read the type-hinting from the parameters of the function.
     */
    private function getParametersDefinition($className, \ReflectionFunctionAbstract $constructor)
    {
        $parameters = [];

        foreach ($constructor->getParameters() as $index => $parameter) {
            // Skip optional parameters
            if ($parameter->isOptional()) {
                continue;
            }

            $parameterClass = $parameter->getClass();

            if ($parameterClass) {
                $entryReference = new EntryReference($parameterClass->getName());
            } else {
                $entryReference = new EntryReference(
                    str_replace(
                        ['className', 'parameter'],
                        [$className, $parameter->getName()],
                        $this->scalarEntryFormat
                    )
                );
            }

            $parameters[$index] = $entryReference;
        }

        return $parameters;
    }
}
