<?php

namespace DI;

/**
 * Compiled version of the dependency injection container.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
abstract class CompiledContainer extends Container
{
    /**
     * {@inheritdoc}
     */
    public function get($name)
    {
        // Try to find the entry in the singleton map
        if (isset($this->singletonEntries[$name]) || array_key_exists($name, $this->singletonEntries)) {
            return $this->singletonEntries[$name];
        }

        $method = static::METHOD_MAPPING[$name] ?? null;

        // If it's a compiled entry, then there is a method in this class
        if ($method !== null) {
            $value = $this->$method();

            // Store the entry to always return it without recomputing it
            $this->singletonEntries[$name] = $value;

            return $value;
        }

        return parent::get($name);
    }

    /**
     * {@inheritdoc}
     */
    public function has($name)
    {
        if (! is_string($name)) {
            throw new \InvalidArgumentException(sprintf(
                'The name parameter must be of type string, %s given',
                is_object($name) ? get_class($name) : gettype($name)
            ));
        }

        // The parent method is overridden to check in our array, it avoids resolving definitions
        if (isset(static::METHOD_MAPPING[$name])) {
            return true;
        }

        return parent::has($name);
    }
}