<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.com/PHP-DI/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI;

use DI\Definition\AnnotationDefinitionReader;
use DI\Definition\ArrayDefinitionReader;
use DI\Definition\CachedDefinitionReader;
use DI\Definition\CombinedDefinitionReader;
use DI\Definition\DefinitionReader;
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
     * Reader merging definitions of sub-readers
     * @var CachedDefinitionReader
     */
    private $cachedReader;

    /**
     * Reader merging definitions of sub-readers
     * @var CombinedDefinitionReader
     */
    private $combinedReader;

    /**
     * Keep a reference to the annotation reader to ensure only one is used
     * @var AnnotationDefinitionReader|null
     */
    private $annotationReader;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->combinedReader = new CombinedDefinitionReader();
    }

    /**
     * @return DefinitionReader
     */
    public function getDefinitionReader()
    {
        // If we use the cache
        if ($this->cachedReader !== null) {
            return $this->cachedReader;
        } else {
            return $this->combinedReader;
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
            if ($this->annotationReader === null) {
                $this->annotationReader = new AnnotationDefinitionReader();
                $this->combinedReader->addReader($this->annotationReader);
            }
        } else {
            // Disable
            if ($this->annotationReader !== null) {
                $this->combinedReader->removeReader($this->annotationReader);
                unset($this->annotationReader);
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
        $reader = new ArrayDefinitionReader();
        $reader->addDefinitions($definitions);
        $this->combinedReader->addReader($reader);
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

            $reader = new ArrayDefinitionReader();
            $reader->addDefinitions($definitions);
            $this->combinedReader->addReader($reader);
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
            if ($this->cachedReader === null) {
                $this->cachedReader = new CachedDefinitionReader($this->combinedReader, $cache, $debug);
            } else {
                $this->cachedReader->setCache($cache);
                $this->cachedReader->setDebug($debug);
            }
        } else {
            // Disable
            if ($this->cachedReader !== null) {
                unset($this->cachedReader);
            }
        }
    }

}
