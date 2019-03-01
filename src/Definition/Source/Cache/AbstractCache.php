<?php

namespace DI\Definition\Source\Cache;

use DI\Definition\AutowireDefinition;
use DI\Definition\Definition;
use DI\Definition\ObjectDefinition;
use DI\Definition\Source\DefinitionSource;
use LogicException;

/**
 * The AbstractCache provides all the boilerplate logic for different cache implementations.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 * @author Benjamin Zikarsky <benjamin.zikarsky@jaumo.com>
 */
abstract class AbstractCache implements Cache
{
    /**
     * @var DefinitionSource
     */
    private $cachedSource;

    /**
     * The constructor is protected - use `static::create(..)` instead.
     *
     * @param DefinitionSource $cachedSource
     */
    protected function __construct(DefinitionSource $cachedSource)
    {
        $this->cachedSource = $cachedSource;
    }

    /** {@inheritdoc} */
    public static function create(DefinitionSource $source) : Cache
    {
        return new static($source);
    }

    /** {@inheritdoc} */
    public function getDefinition(string $name)
    {
        $definition = $this->fetch($name);

        if ($definition === false) {
            $definition = $this->cachedSource->getDefinition($name);

            // Update the cache
            if ($this->shouldBeCached($definition)) {
                $this->store($name, $definition);
            }
        }

        return $definition;
    }

    /**
     * Fetch a definition from cache.
     *
     * Since both nulls and objects can be cached, a cache-miss is indicated by a strictly typed boolean false.
     *
     * @param string $name
     * @return false|Definition|null
     */
    abstract protected function fetch(string $name);

    /**
     * Store a definition with given name in cache.
     *
     * @param string $name
     * @param Definition $definition
     */
    abstract protected function store(string $name, Definition $definition);

    /**
     * Used only for the compilation so we can skip the cache safely.
     */
    public function getDefinitions() : array
    {
        return $this->cachedSource->getDefinitions();
    }

    /** {@inheritdoc} */
    public function addDefinition(Definition $definition)
    {
        throw new LogicException('You cannot set a definition at runtime on a container that has caching '
            . 'enabled. Doing so would risk caching the definition for the next execution, where it might be different. '
            . 'You can either put your definitions in a file, remove the cache or ->set() a raw value directly (PHP '
            . 'object, string, int, ...) instead of a PHP-DI definition.');
    }

    /**
     * Check whether a definition should be cached.
     *
     * Currently it makes only sense to cache missing, object- and autowired definitions.
     *
     * @param Definition|null $definition
     * @return bool
     */
    private function shouldBeCached(Definition $definition = null) : bool
    {
        return
            // Cache missing definitions
            ($definition === null)
            // Object definitions are used with `make()`
            || ($definition instanceof ObjectDefinition)
            // Autowired definitions cannot be all compiled and are used with `make()`
            || ($definition instanceof AutowireDefinition);
    }
}
