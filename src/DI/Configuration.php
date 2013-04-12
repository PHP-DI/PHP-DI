<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.io/PHP-DI/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI;

use DI\Definition\Source\AnnotationDefinitionSource;
use DI\Definition\Source\ArrayDefinitionSource;
use DI\Definition\Source\CachedDefinitionSource;
use DI\Definition\Source\CombinedDefinitionSource;
use DI\Definition\Source\DefinitionSource;
use DI\Definition\Source\ReflectionDefinitionSource;
use Doctrine\Common\Cache\Cache;

/**
 * Configuration of the dependency injection container
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class Configuration
{

    const CONFIGURATION_PHP = 'php';

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
    public function addDefinitions(array $definitions)
    {
        $arraySource = new ArrayDefinitionSource();
        $arraySource->addDefinitions($definitions);
        $this->combinedSource->addSource($arraySource);
    }

    /**
     * Add definitions contained in a file
     *
     * @param string $filename PHP file returning an array
     * @param string $type
     * @throws \InvalidArgumentException
     * @throws \Exception
     */
    public function addDefinitionsFromFile($filename, $type = self::CONFIGURATION_PHP)
    {
        if (!file_exists($filename)) {
            throw new \InvalidArgumentException("The file '$filename' doesn't exist");
        }

        if ($type === self::CONFIGURATION_PHP) {
            // Read file
            $definitions = include $filename;

            if (!is_array($definitions)) {
                throw new \Exception("The file '$filename' doesn't return a PHP array");
            }

            $source = new ArrayDefinitionSource();
            $source->addDefinitions($definitions);
            $this->combinedSource->addSource($source);
            return;
        }

        throw new \InvalidArgumentException("Unknown configuration type '$type'");
    }

    /**
     * Enables the use of a cache for all the other definition sources
     *
     * @param Cache|null $cache
     * @param boolean    $debug If true, changes in the files will be tracked to update the cache automatically.
     * Disable in production for better performances.
     */
    public function setCache(Cache $cache = null, $debug = false)
    {
        if ($cache !== null) {
            // Enable
            if ($this->cachedSource === null) {
                $this->cachedSource = new CachedDefinitionSource($this->combinedSource, $cache, $debug);
            } else {
                $this->cachedSource->setCache($cache);
                $this->cachedSource->setDebug($debug);
            }
        } else {
            // Disable
            if ($this->cachedSource !== null) {
                unset($this->cachedSource);
            }
        }
    }

}
