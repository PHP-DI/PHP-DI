<?php

namespace DI\Definition\Source;

use DI\Definition\InteropDefinition;
use DI\Definition\ObjectDefinition;

/**
 * Reads definitions from a Interop\Container\ServiceProvider class.
 *
 * @see Interop\Container\ServiceProvider
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class InteropServiceProvider implements DefinitionSource
{
    /**
     * @var string
     */
    private $serviceProvider;

    /**
     * @var array|null
     */
    private $entries;

    /**
     * @param string $serviceProvider Name of a class implementing Interop\Container\ServiceProvider.
     */
    public function __construct($serviceProvider)
    {
        $this->serviceProvider = $serviceProvider;
    }

    public function getDefinition($name)
    {
        $class = $this->serviceProvider;

        if ($this->entries === null) {
            $this->entries = call_user_func([$class, 'getServices']);
        }

        if (!isset($this->entries[$name])) {
            return null;
        }

        return new InteropDefinition($name, $class, $this->entries[$name]);
    }
}
