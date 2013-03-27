<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.com/PHP-DI/
 * @copyright 2012 Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Definition;

use Doctrine\Common\Cache\Cache;

/**
 * Caches the results of another Definition Reader
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class CachedDefinitionReader implements DefinitionReader
{

    /**
     * Prefix for cache key, to avoid conflicts with other systems using the same cache
     * @var string
     */
    private static $CACHE_PREFIX = 'DI\\Definition';

    /**
     * @var DefinitionReader
     */
    private $definitionReader;

    /**
     * @var Cache
     */
    private $cache;

    /**
     * If true, changes in the files will be tracked to update the cache automatically.
     * Disable in production for better performances.
     * @var boolean
     */
    private $debug;

    /**
     * Construct the cache
     *
     * @param DefinitionReader $definitionReader
     * @param Cache            $cache
     * @param boolean          $debug If true, changes in the files will be tracked to update the cache automatically.
     * Disable in production for better performances.
     */
    public function __construct(DefinitionReader $definitionReader, Cache $cache, $debug = false)
    {
        $this->definitionReader = $definitionReader;
        $this->cache = $cache;
        $this->debug = (boolean) $debug;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefinition($name)
    {
        $result = $this->fetchFromCache($name);
        if (!$result) {
            $result = $this->definitionReader->getDefinition($name);
            $this->saveToCache($name, $result);
        }
        return $result;
    }

    /**
     * @return DefinitionReader
     */
    public function getDefinitionReader()
    {
        return $this->definitionReader;
    }

    /**
     * @param DefinitionReader $reader
     */
    public function setDefinitionReader(DefinitionReader $reader)
    {
        $this->definitionReader = $reader;
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
     * If true, changes in the files will be tracked to update the cache automatically.
     * Disable in production for better performances.
     * @return boolean
     */
    public function getDebug()
    {
        return $this->debug;
    }

    /**
     * If true, changes in the files will be tracked to update the cache automatically.
     * Disable in production for better performances.
     * @param boolean $debug
     */
    public function setDebug($debug)
    {
        $this->debug = (boolean) $debug;
    }

    /**
     * Fetches a value from the cache
     *
     * @param string $classname The class name
     * @return mixed|boolean The cached value or false when the value is not in cache
     */
    private function fetchFromCache($classname)
    {
        $cacheKey = self::$CACHE_PREFIX . $classname;
        if (($data = $this->cache->fetch($cacheKey)) !== false) {
            if (!$this->debug || $this->isCacheFresh($cacheKey, $classname)) {
                return $data;
            }
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
        if ($this->debug) {
            $this->cache->save('[C]' . $cacheKey, time());
        }
    }

    /**
     * Check if cache is fresh
     *
     * @param string $cacheKey
     * @param string $classname
     * @return boolean
     */
    private function isCacheFresh($cacheKey, $classname)
    {
        $class = new \ReflectionClass($classname);
        if (false === $filename = $class->getFilename()) {
            return true;
        }
        return $this->cache->fetch('[C]' . $cacheKey) >= filemtime($filename);
    }

}
