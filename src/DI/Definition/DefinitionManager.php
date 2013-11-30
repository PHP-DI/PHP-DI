<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.io/PHP-DI/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Definition;

use DI\Definition\Source\AnnotationDefinitionSource;
use DI\Definition\Source\ArrayDefinitionSource;
use DI\Definition\Source\CombinedDefinitionSource;
use DI\Definition\Source\DefinitionSource;
use DI\Definition\Source\ReflectionDefinitionSource;
use DI\Definition\Source\SimpleDefinitionSource;
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
     * @var DefinitionSource[]
     */
    private $otherSources = array();

    public function __construct($useReflection = true, $useAnnotations = true)
    {
        $this->simpleSource = new SimpleDefinitionSource();
        if ($useReflection) {
            $this->reflectionSource = new ReflectionDefinitionSource();
        }
        if ($useAnnotations) {
            $this->annotationSource = new AnnotationDefinitionSource();
        }

        $this->updateCombinedSource();
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
        // Enable
        if ($bool && $this->reflectionSource === null) {
            $this->reflectionSource = new ReflectionDefinitionSource();
            $this->updateCombinedSource();
        // Disable
        } elseif (!$bool && $this->reflectionSource !== null) {
            $this->reflectionSource = null;
            $this->updateCombinedSource();
        }
    }

    /**
     * Enable or disable the use of annotations
     *
     * @param boolean $bool
     */
    public function useAnnotations($bool)
    {
        // Enable
        if ($bool && $this->annotationSource === null) {
            $this->annotationSource = new AnnotationDefinitionSource();
            $this->updateCombinedSource();
        // Disable
        } elseif (!$bool && $this->annotationSource !== null) {
            $this->annotationSource = null;
            $this->updateCombinedSource();
        }
    }

    /**
     * Add a source of definitions
     *
     * @param DefinitionSource $definitionSource
     */
    public function addDefinitionSource(DefinitionSource $definitionSource)
    {
        $this->otherSources[] = $definitionSource;

        $this->updateCombinedSource();
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

        $this->otherSources[] = $arraySource;

        $this->updateCombinedSource();
    }

    /**
     * Add a single definition
     *
     * @param Definition $definition
     */
    public function addDefinition(Definition $definition)
    {
        $this->simpleSource->addDefinition($definition);
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

    /**
     * Builds the combined source
     *
     * Builds from scratch every time because the order of the sources is important.
     */
    private function updateCombinedSource()
    {
        // Sources are added from highest priority to least priority
        $this->combinedSource = new CombinedDefinitionSource();

        $this->combinedSource->addSource($this->simpleSource);

        // Traverses the array reversed so that the latest added is first
        foreach (array_reverse($this->otherSources) as $arraySource) {
            $this->combinedSource->addSource($arraySource);
        }

        if ($this->annotationSource) {
            $this->combinedSource->addSource($this->annotationSource);
        }

        if ($this->reflectionSource) {
            $this->combinedSource->addSource($this->reflectionSource);
        }
    }
}
