<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.io/PHP-DI/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI;

use DI\Definition\DefinitionManager;
use DI\Definition\FileLoader\DefinitionFileLoader;
use Doctrine\Common\Cache\Cache;
use InvalidArgumentException;
use ProxyManager\Configuration;
use ProxyManager\Factory\LazyLoadingValueHolderFactory;
use ProxyManager\GeneratorStrategy\EvaluatingGeneratorStrategy;

/**
 * Fluent helper to create a Container
 *
 * @since 3.2
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class ContainerBuilder
{

    /**
     * @var boolean
     */
    private $useReflection = true;

    /**
     * @var boolean
     */
    private $useAnnotations = true;

    /**
     * @var boolean
     */
    private $definitionsValidation;

    /**
     * @var Cache
     */
    private $cache;

    /**
     * @var DefinitionFileLoader[]
     */
    private $fileLoaders = array();

    /**
     * If true, write the proxies to disk to improve performances.
     * @var boolean
     */
    private $writeProxiesToFile = false;

    /**
     * Directory where to write the proxies (if $writeProxiesToFile is enabled)
     * @var string
     */
    private $proxyDirectory;

    /**
     * @return Container
     */
    public function build()
    {
        // Definition manager
        $definitionManager = new DefinitionManager();
        $definitionManager->useReflection($this->useReflection);
        $definitionManager->useAnnotations($this->useAnnotations);
        if ($this->definitionsValidation) {
            $definitionManager->setDefinitionsValidation($this->definitionsValidation);
        }
        if ($this->cache) {
            $definitionManager->setCache($this->cache);
        }
        foreach ($this->fileLoaders as $fileLoader) {
            $definitionManager->addDefinitionsFromFile($fileLoader);
        }

        // Proxy factory
        $config = new Configuration();
        $config->setAutoGenerateProxies(true);
        if ($this->writeProxiesToFile) {
            $config->setProxiesTargetDir($this->proxyDirectory);
            spl_autoload_register($config->getProxyAutoloader());
        } else {
            $config->setGeneratorStrategy(new EvaluatingGeneratorStrategy());
        }

        $container = new Container($definitionManager, null, new LazyLoadingValueHolderFactory($config));

        return $container;
    }

    /**
     * Enable or disable the use of reflection
     *
     * By default, enabled
     * @param boolean $bool
     * @return ContainerBuilder
     */
    public function useReflection($bool)
    {
        $this->useReflection = $bool;
        return $this;
    }

    /**
     * Enable or disable the use of annotations
     *
     * By default, enabled
     * @param $bool
     * @return ContainerBuilder
     */
    public function useAnnotations($bool)
    {
        $this->useAnnotations = $bool;
        return $this;
    }

    /**
     * Enables/disables the validation of the definitions
     *
     * By default, disabled
     * @param bool $bool
     * @return ContainerBuilder
     */
    public function setDefinitionsValidation($bool)
    {
        $this->definitionsValidation = $bool;
        return $this;
    }

    /**
     * Enables the use of a cache for the definitions
     *
     * @param Cache $cache Cache backend to use
     * @return ContainerBuilder
     */
    public function setDefinitionCache(Cache $cache)
    {
        $this->cache = $cache;
        return $this;
    }

    /**
     * Add definitions contained in a file
     *
     * @param DefinitionFileLoader $definitionFileLoader
     * @return ContainerBuilder
     */
    public function addDefinitionsFromFile(DefinitionFileLoader $definitionFileLoader)
    {
        $this->fileLoaders[] = $definitionFileLoader;
        return $this;
    }

    /**
     * Configure the proxy generation
     *
     * For dev environment, use writeProxiesToFile(false) (default configuration)
     * For production environment, use writeProxiesToFile(true, 'tmp/proxies')
     *
     * @param boolean     $writeToFile If true, write the proxies to disk to improve performances
     * @param string|null $proxyDirectory Directory where to write the proxies
     * @return ContainerBuilder
     *
     * @throws InvalidArgumentException when writeToFile is set to true and the proxy directory is null
     */
    public function writeProxiesToFile($writeToFile, $proxyDirectory = null)
    {
        $this->writeProxiesToFile = $writeToFile;

        if ($writeToFile && $proxyDirectory === null) {
            throw new InvalidArgumentException("The proxy directory must be specified if you want to write proxies on disk");
        }
        $this->proxyDirectory = $proxyDirectory;

        return $this;
    }

}
