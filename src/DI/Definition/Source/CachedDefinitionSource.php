<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.io/PHP-DI/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Definition\Source;

use Doctrine\Common\Cache\Cache;

/**
 * Caches the results of another Definition source
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class CachedDefinitionSource implements DefinitionSource
{

    /**
     * Prefix for cache key, to avoid conflicts with other systems using the same cache
     * @var string
     */
    private static $CACHE_PREFIX = 'DI\\Definition';

    /**
     * @var DefinitionSource
     */
    private $definitionSource;

    /**
     * @var Cache
     */
    private $cache;

    /**
     * Construct the cache
     *
     * @param DefinitionSource $definitionSource
     * @param Cache            $cache
     */
    public function __construct(DefinitionSource $definitionSource, Cache $cache)
    {
        $this->definitionSource = $definitionSource;
        $this->cache = $cache;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefinition($name)
    {
        $definition = $this->fetchFromCache($name);
        if (!$definition) {
            $definition = $this->definitionSource->getDefinition($name);
            if ($definition === null || ($definition && $definition->isCacheable())) {
                $this->saveToCache($name, $definition);
            }
        }
        return $definition;
    }

    /**
     * @return DefinitionSource
     */
    public function getDefinitionSource()
    {
        return $this->definitionSource;
    }

    /**
     * @param DefinitionSource $source
     */
    public function setDefinitionSource(DefinitionSource $source)
    {
        $this->definitionSource = $source;
    }

    /**
     * @return Cache
     */
    public function getCache()
    {
        return $this->cache;
    }

    /**
     * @param Cache $cache
     */
    public function setCache(Cache $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Fetches a value from the cache
     *
     * @param string $name Entry name
     * @return mixed|boolean The cached value or false when the value is not in cache
     */
    private function fetchFromCache($name)
    {
        $cacheKey = self::$CACHE_PREFIX . $name;
        if (($data = $this->cache->fetch($cacheKey)) !== false) {
            return $data;
        }
        return false;
    }

    /**
     * Saves a value to the cache
     *
     * @param string $classname The cache key
     * @param mixed  $value     The value
     */
    private function saveToCache($classname, $value)
    {
        $cacheKey = self::$CACHE_PREFIX . $classname;
        $this->cache->save($cacheKey, $value);
    }

}
