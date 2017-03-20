<?php

namespace DI\Definition;

use DI\Definition\ObjectDefinition\MethodInjection;

/**
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class AutowireDefinition extends ObjectDefinition implements HasSubDefinition
{
    /**
     * {@inheritdoc}
     */
    public function getSubDefinitionName()
    {
        return $this->getClassName();
    }

    /**
     * {@inheritdoc}
     *
     * @deprecated
     */
    public function setSubDefinition(Definition $definition)
    {
        if (! $definition instanceof self) {
            return;
        }

        // The current prevails
        if ($this->className === null) {
            $this->setClassName($definition->className);
        }
        if ($this->scope === null) {
            $this->scope = $definition->scope;
        }
        if ($this->lazy === null) {
            $this->lazy = $definition->lazy;
        }

        // Merge constructor injection
        $this->mergeConstructorInjection($definition);

        // Merge property injections
        $this->mergePropertyInjections($definition);

        // Merge method injections
        $this->mergeMethodInjections($definition);
    }

    public function setDefaultConstructorInjection(MethodInjection $injection)
    {
        if ($this->constructorInjection !== null) {
            // Merge
            $this->constructorInjection->merge($injection);
        } else {
            // Set
            $this->constructorInjection = $injection;
        }
    }

    private function mergeConstructorInjection(ObjectDefinition $definition)
    {
        if ($definition->getConstructorInjection() !== null) {
            if ($this->constructorInjection !== null) {
                // Merge
                $this->constructorInjection->merge($definition->getConstructorInjection());
            } else {
                // Set
                $this->constructorInjection = $definition->getConstructorInjection();
            }
        }
    }

    private function mergePropertyInjections(ObjectDefinition $definition)
    {
        foreach ($definition->propertyInjections as $propertyName => $propertyInjection) {
            if (! isset($this->propertyInjections[$propertyName])) {
                // Add
                $this->propertyInjections[$propertyName] = $propertyInjection;
            }
        }
    }

    private function mergeMethodInjections(ObjectDefinition $definition)
    {
        foreach ($definition->methodInjections as $methodName => $calls) {
            if (array_key_exists($methodName, $this->methodInjections)) {
                $this->mergeMethodCalls($calls, $methodName);
            } else {
                // Add
                $this->methodInjections[$methodName] = $calls;
            }
        }
    }

    private function mergeMethodCalls(array $calls, $methodName)
    {
        foreach ($calls as $index => $methodInjection) {
            // Merge
            if (array_key_exists($index, $this->methodInjections[$methodName])) {
                // Merge
                $this->methodInjections[$methodName][$index]->merge($methodInjection);
            } else {
                // Add
                $this->methodInjections[$methodName][$index] = $methodInjection;
            }
        }
    }
}
