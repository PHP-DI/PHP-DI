<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Definition;

use DI\Definition\Source\ArrayDefinitionSource;
use DI\Definition\Source\CallableDefinitionSource;
use DI\Definition\Source\DefinitionSource;
use DI\Definition\Source\ReflectionDefinitionSource;
use Doctrine\Common\Cache\Cache;

/**
 * Manages definitions
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 * @author Domenic Muskulus <domenic@muskulus.eu>
 */
class DefinitionManager
{
    /**
     * Prefix for cache key, to avoid conflicts with other systems using the same cache
     * @var string
     */
    const CACHE_PREFIX = 'DI\\Definition';

    /**
     * @var Cache|null
     */
    private $cache;

    /**
     * @var ArrayDefinitionSource
     */
    private $source;

    /**
     * @var CallableDefinitionSource
     */
    private $callableSource;

    public function __construct(DefinitionSource $source = null)
    {
        $this->source = new ArrayDefinitionSource();
        $this->callableSource = new ReflectionDefinitionSource();

        if ($source) {
            $this->source->chain($source);
        }
    }

    /**
     * Returns DI definition for the entry name
     *
     * @param string $name
     *
     * @return Definition|null
     */
    public function getDefinition($name)
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

    /**
     * Returns DI definition for the callable.
     *
     * @param string $callable
     *
     * @return FunctionCallDefinition
     */
    public function getCallableDefinition($callable)
    {
        return $this->callableSource->getCallableDefinition($callable);
    }

    /**
     * Add a single definition
     *
     * @param Definition $definition
     */
    public function addDefinition(Definition $definition)
    {
        $this->source->addDefinition($definition);
    }

    /**
     * @return Cache|null
     */
    public function getCache()
    {
        return $this->cache;
    }

    /**
     * Enables the use of a cache
     *
     * @param Cache|null $cache
     */
    public function setCache(Cache $cache = null)
    {
        $this->cache = $cache;
    }

    /**
     * Fetches a definition from the cache
     *
     * @param string $name Entry name
     * @return Definition|null|boolean The cached definition, null or false if the value is not already cached
     */
    private function fetchFromCache($name)
    {
        if ($this->cache === null) {
            return false;
        }

        $cacheKey = self::CACHE_PREFIX . $name;
        if (($data = $this->cache->fetch($cacheKey)) !== false) {
            return $data;
        }
        return false;
    }

    /**
     * Saves a definition to the cache
     *
     * @param string          $name Entry name
     * @param Definition|null $definition
     */
    private function saveToCache($name, Definition $definition = null)
    {
        if ($this->cache === null) {
            return;
        }

        $cacheKey = self::CACHE_PREFIX . $name;
        $this->cache->save($cacheKey, $definition);
    }
}
