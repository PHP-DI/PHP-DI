<?php

namespace DI\Definition\Source;

use DI\Definition\ArrayDefinition;
use DI\Definition\DecoratorDefinition;
use DI\Definition\Definition;
use DI\Definition\Exception\DefinitionException;
use DI\Definition\FactoryDefinition;
use DI\Definition\Helper\DefinitionHelper;
use DI\Definition\InteropDefinition;
use DI\Definition\ObjectDefinition;
use DI\Definition\ValueDefinition;
use Interop\Container\ContainerInterface;

/**
 * Reads definitions from a standard service provider.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class ServiceProvider implements DefinitionSource
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
     * @param string $serviceProvider Class name.
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
