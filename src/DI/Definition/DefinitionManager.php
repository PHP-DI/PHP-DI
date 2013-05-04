<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.io/PHP-DI/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Definition;

use DI\Definition\Definition;
use DI\Definition\Source\AnnotationDefinitionSource;
use DI\Definition\Source\ArrayDefinitionSource;
use DI\Definition\Source\CombinedDefinitionSource;
use DI\Definition\Source\ReflectionDefinitionSource;
use DI\Definition\Source\SimpleDefinitionSource;
use DI\Definition\FileLoader\DefinitionFileLoader;
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
     * Source merging definitions of sub-sources
     * @var CombinedDefinitionSource
     */
    private $combinedSource;

    /**
     * Keep a reference to the simple source to add definitions to it
     * @var SimpleDefinitionSource
     */
    private $simpleSource;

    /**
     * Keep a reference to the reflection source to ensure only one is used
     * @var ReflectionDefinitionSource|null
     */
    private $reflectionSource;

    /**
     * Keep a reference to the annotation source to ensure only one is used
     * @var AnnotationDefinitionSource|null
     */
    private $annotationSource;

    /**
     * Enables/disable the validation of the definitions
     * @var bool
     */
    private $definitionValidation = false;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->combinedSource = new CombinedDefinitionSource();
        $this->simpleSource = new SimpleDefinitionSource();
        $this->combinedSource->addSource($this->simpleSource);
    }

    /**
     * Returns DI definition for the entry name
     * @param string $name
     * @return Definition|null
     */
    public function getDefinition($name)
    {
        // Look in cache first
        $definition = $this->fetchFromCache($name);

        if ($definition === false) {
            $definition = $this->combinedSource->getDefinition($name);

            // If alias, merge definition with alias
            if ($definition instanceof ClassDefinition && $definition->isAlias()) {
                $implementationDefinition = $this->getDefinition($definition->getClassName());

                if ($implementationDefinition) {
                    $definition->merge($implementationDefinition);
                }
            }

            // Save to cache
            if ($definition === null || ($definition && $definition->isCacheable())) {
                $this->saveToCache($name, $definition);
            }
        }

        return $definition;
    }

    /**
     * Enable or disable the use of reflection
     *
     * @param boolean $bool
     */
    public function useReflection($bool)
    {
        if ($bool) {
            // Enable
            if ($this->reflectionSource === null) {
                $this->reflectionSource = new ReflectionDefinitionSource();
                $this->combinedSource->addSource($this->reflectionSource);
            }
        } else {
            // Disable
            if ($this->reflectionSource !== null) {
                $this->combinedSource->removeSource($this->reflectionSource);
                unset($this->reflectionSource);
            }
        }
    }

    /**
     * Enable or disable the use of annotations
     *
     * @param boolean $bool
     */
    public function useAnnotations($bool)
    {
        if ($bool) {
            // Enable
            if ($this->annotationSource === null) {
                $this->annotationSource = new AnnotationDefinitionSource();
                $this->combinedSource->addSource($this->annotationSource);
            }
        } else {
            // Disable
            if ($this->annotationSource !== null) {
                $this->combinedSource->removeSource($this->annotationSource);
                unset($this->annotationSource);
            }
        }
    }

    /**
     * Add definitions from an array
     *
     * @param array $definitions
     */
    public function addArrayDefinitions(array $definitions)
    {
        $arraySource = new ArrayDefinitionSource();
        $arraySource->addDefinitions($definitions);
        $this->combinedSource->addSource($arraySource);
    }

    /**
     * Add definitions from an array
     *
     * @param array $definitions
     */
    public function addDefinition(Definition $definition)
    {
        $this->simpleSource->addDefinition($definition);
    }

    /**
     * Add definitions contained in a file
     *
     * @param \DI\Definition\FileLoader\DefinitionFileLoader $definitionFileLoader
     * @throws \InvalidArgumentException
     */
    public function addDefinitionsFromFile(DefinitionFileLoader $definitionFileLoader)
    {
        $definitions = $definitionFileLoader->load($this->definitionValidation);

        if (!is_array($definitions)) {
            throw new \InvalidArgumentException(get_class($definitionFileLoader) . " must return an array.");
        }

        $source = new ArrayDefinitionSource();
        $source->addDefinitions($definitions);
        $this->combinedSource->addSource($source);
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
     * Enables/disables the validation of the definitions
     *
     * By default, disabled
     * @param bool $bool
     */
    public function setDefinitionsValidation($bool)
    {
        $this->definitionValidation = (bool) $bool;
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
