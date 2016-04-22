<?php

namespace DI\Definition\Source;

use DI\Definition\Exception\DefinitionException;
use DI\Definition\InteropDefinition;
use DI\Definition\ObjectDefinition;
use DI\Definition\StaticInteropDefinition;
use Interop\Container\ServiceProvider;
use Invoker\Reflection\CallableReflection;

/**
 * Reads definitions from a Interop\Container\ServiceProvider class.
 *
 * @see Interop\Container\ServiceProvider
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class InteropServiceProvider implements DefinitionSource
{
    /**
     * @var int
     */
    private $serviceProviderKey;

    /**
     * @var ServiceProvider
     */
    private $serviceProvider;

    /**
     * @var array|null
     */
    private $entries;

    /**
     * @param ServiceProvider $serviceProvider
     */
    public function __construct($serviceProviderKey, ServiceProvider $serviceProvider)
    {
        $this->serviceProviderKey = $serviceProviderKey;
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

        if (!is_callable($this->entries[$name])) {
            // TODO: create a special exception.
            throw new \Exception(sprintf('Entry %s should be a callable.', $name));
        }

        $reflection = CallableReflection::create($this->entries[$name]);

        // Let's optimize for public static function calls
        if ($reflection instanceof \ReflectionMethod && $reflection->isPublic() && $reflection->isStatic()) {
            return new StaticInteropDefinition($name, $reflection->getDeclaringClass()->getName(), $reflection->getName());
        } else {
            return new InteropDefinition($name, $this->serviceProviderKey);
        }


    }
}
