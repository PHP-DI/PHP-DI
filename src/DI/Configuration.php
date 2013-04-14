<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.io/PHP-DI/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI;

use DI\Definition\Definition;
use DI\Definition\Source\AnnotationDefinitionSource;
use DI\Definition\Source\ArrayDefinitionSource;
use DI\Definition\Source\CachedDefinitionSource;
use DI\Definition\Source\CombinedDefinitionSource;
use DI\Definition\Source\DefinitionSource;
use DI\Definition\Source\ReflectionDefinitionSource;
use DI\Definition\Source\SimpleDefinitionSource;
use DI\Loader\DefinitionFileLoader;
use Doctrine\Common\Cache\Cache;

/**
 * Configuration of the dependency injection container
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 * @author Domenic Muskulus <domenic@muskulus.eu>
 */
class Configuration
{

    /**
     * Caches the combinedSource
     * @var CachedDefinitionSource
     */
    private $cachedSource;

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
     * Constructor
     */
    public function __construct()
    {
        $this->combinedSource = new CombinedDefinitionSource();
        $this->simpleSource = new SimpleDefinitionSource();
        $this->combinedSource->addSource($this->simpleSource);
    }

    /**
     * @return DefinitionSource
     */
    public function getDefinitionSource()
    {
        // If we use the cache
        if ($this->cachedSource !== null) {
            return $this->cachedSource;
        } else {
            return $this->combinedSource;
        }
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
     * @param Loader\DefinitionFileLoader $definitionFileLoader
     * @throws \InvalidArgumentException
     */
    public function addDefinitionsFromFile(DefinitionFileLoader $definitionFileLoader)
    {
        $definitions = $definitionFileLoader->load();

        if (!is_array($definitions)) {
            throw new \InvalidArgumentException(get_class($definitionFileLoader) .  " must return an array.");
        }

        $source = new ArrayDefinitionSource();
        $source->addDefinitions($definitions);
        $this->combinedSource->addSource($source);
    }

    /**
     * Enables the use of a cache for all the other definition sources
     *
     * @param Cache|null $cache
     */
    public function setCache(Cache $cache = null)
    {
        if ($cache !== null) {
            // Enable
            if ($this->cachedSource === null) {
                $this->cachedSource = new CachedDefinitionSource($this->combinedSource, $cache);
            } else {
                $this->cachedSource->setCache($cache);
            }
        } else {
            // Disable
            if ($this->cachedSource !== null) {
                unset($this->cachedSource);
            }
        }
    }

}
