<?php

namespace DI;

use DI\Definition\AliasDefinition;
use DI\Definition\ArrayDefinitionExtension;
use DI\Definition\EnvironmentVariableDefinition;
use DI\Definition\Helper\FactoryDefinitionHelper;
use DI\Definition\Helper\ObjectDefinitionHelper;
use DI\Definition\StringDefinition;
use DI\Definition\ValueDefinition;

if (! function_exists('DI\value')) {
    /**
     * Helper for defining a value.
     *
     * @param mixed $value
     *
     * @return ValueDefinition
     */
    function value($value)
    {
        return new ValueDefinition($value);
    }
}

if (! function_exists('DI\object')) {
    /**
     * Helper for defining an object.
     *
     * @param string|null $className Class name of the object.
     *                               If null, the name of the entry (in the container) will be used as class name.
     *
     * @return ObjectDefinitionHelper
     */
    function object($className = null)
    {
        return new ObjectDefinitionHelper($className);
    }
}

if (! function_exists('DI\factory')) {
    /**
     * Helper for defining a container entry using a factory function/callable.
     *
     * @param callable $factory The factory is a callable that takes the container as parameter
     *                          and returns the value to register in the container.
     *
     * @return FactoryDefinitionHelper
     */
    function factory($factory)
    {
        return new FactoryDefinitionHelper($factory);
    }
}

if (! function_exists('DI\decorate')) {
    /**
     * Decorate the previous definition using a callable.
     *
     * Example:
     *
     *     'foo' => decorate(function ($foo, $container) {
     *         return new CachedFoo($foo, $container->get('cache'));
     *     })
     *
     * @param callable $callable The callable takes the decorated object as first parameter and
     *                           the container as second.
     *
     * @return FactoryDefinitionHelper
     */
    function decorate($callable)
    {
        return new FactoryDefinitionHelper($callable, true);
    }
}

if (! function_exists('DI\get')) {
    /**
     * Helper for referencing another container entry.
     *
     * @param string $entryName
     *
     * @return AliasDefinition
     */
    function get($entryName)
    {
        return new AliasDefinition($entryName);
    }
}

if (! function_exists('DI\link')) {
    /**
     * Helper for referencing another container entry.
     *
     * @deprecated \DI\link() has been replaced by \DI\get()
     *
     * @param string $entryName
     *
     * @return AliasDefinition
     */
    function link($entryName)
    {
        return new AliasDefinition($entryName);
    }
}

if (! function_exists('DI\env')) {
    /**
     * Helper for referencing environment variables.
     *
     * @param string $variableName The name of the environment variable.
     * @param mixed $defaultValue The default value to be used if the environment variable is not defined.
     *
     * @return EnvironmentVariableDefinition
     */
    function env($variableName, $defaultValue = null)
    {
        // Only mark as optional if the default value was *explicitly* provided.
        $isOptional = 2 === func_num_args();

        return new EnvironmentVariableDefinition($variableName, $isOptional, $defaultValue);
    }
}

if (! function_exists('DI\add')) {
    /**
     * Helper for extending another definition.
     *
     * Example:
     *
     *     'log.backends' => DI\add(DI\get('My\Custom\LogBackend'))
     *
     * or:
     *
     *     'log.backends' => DI\add([
     *         DI\get('My\Custom\LogBackend')
     *     ])
     *
     * @param mixed|array $values A value or an array of values to add to the array.
     *
     * @return ArrayDefinitionExtension
     *
     * @since 5.0
     */
    function add($values)
    {
        if (! is_array($values)) {
            $values = [$values];
        }

        return new ArrayDefinitionExtension($values);
    }
}

if (! function_exists('DI\string')) {
    /**
     * Helper for concatenating strings.
     *
     * Example:
     *
     *     'log.filename' => DI\string('{app.path}/app.log')
     *
     * @param string $expression A string expression. Use the `{}` placeholders to reference other container entries.
     *
     * @return StringDefinition
     *
     * @since 5.0
     */
    function string($expression)
    {
        return new StringDefinition((string) $expression);
    }
}
