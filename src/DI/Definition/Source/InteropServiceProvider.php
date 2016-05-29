<?php

namespace DI\Definition\Source;

use DI\Definition\InteropDefinition;
use DI\Definition\ObjectDefinition;
use Interop\Container\ServiceProvider;

/**
 * Reads definitions from a Interop\Container\ServiceProvider class.
 *
 * @see Interop\Container\ServiceProvider
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class InteropServiceProvider implements DefinitionSource
{
    /**
     * @var ServiceProvider
     */
    private $serviceProvider;

    /**
     * @var array|null
     */
    private $entries;

    public function __construct(ServiceProvider $serviceProvider)
    {
        $this->serviceProvider = $serviceProvider;
    }

    public function getDefinition($name)
    {
        if ($this->entries === null) {
            $this->entries = $this->serviceProvider->getServices();
        }

        if (!isset($this->entries[$name])) {
            return null;
        }

        return new InteropDefinition($name, $this->entries[$name]);
    }
}
