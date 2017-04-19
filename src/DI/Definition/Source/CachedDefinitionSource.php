<?php

namespace DI\Definition\Source;

use DI\Definition\CacheableDefinition;
use DI\Definition\Definition;
use Psr\SimpleCache\CacheInterface;

/**
 * Caches another definition source.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class CachedDefinitionSource implements DefinitionSource
{
    /**
     * Prefix for cache key, to avoid conflicts with other systems using the same cache.
     * @var string
     */
    const CACHE_PREFIX = 'DI.Definition.';

    /**
     * @var DefinitionSource
     */
    private $source;

    /**
     * @var CacheInterface
     */
    private $cache;

    public function __construct(DefinitionSource $source, CacheInterface $cache)
    {
        $this->source = $source;
        $this->cache = $cache;
    }

    public function getDefinition(string $name)
    {
        // Look in cache
        $definition = $this->fetchFromCache($name);

        if ($definition === false) {
            $definition = $this->source->getDefinition($name);

            // Save to cache
            if ($definition === null || ($definition instanceof CacheableDefinition)) {
                $this->saveToCache($name, $definition);
            }
        }

        return $definition;
    }

    public function getCache() : CacheInterface
    {
        return $this->cache;
    }

    /**
     * Fetches a definition from the cache.
     *
     * @param string $name Entry name
     * @return Definition|null|bool The cached definition, null or false if the value is not already cached
     */
    private function fetchFromCache(string $name)
    {
        $data = $this->cache->get($this->getCacheKey($name), false);

        if ($data !== false) {
            return $data;
        }

        return false;
    }

    /**
     * Saves a definition to the cache.
     *
     * @param string $name Entry name
     */
    private function saveToCache(string $name, Definition $definition = null)
    {
        $this->cache->set($this->getCacheKey($name), $definition);
    }

    /**
     * Get normalized cache key.
     *
     * @param string $name
     *
     * @return string
     */
    private function getCacheKey(string $name) : string
    {
        return self::CACHE_PREFIX . str_replace('\\', '.', $name);
    }
}
