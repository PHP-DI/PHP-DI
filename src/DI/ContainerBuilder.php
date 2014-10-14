<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI;

use DI\Definition\DefinitionManager;
use DI\Definition\Source\AnnotationDefinitionSource;
use DI\Definition\Source\PHPFileDefinitionSource;
use DI\Definition\Source\ReflectionDefinitionSource;
use Doctrine\Common\Cache\Cache;
use Interop\Container\ContainerInterface as ContainerInteropInterface;
use InvalidArgumentException;
use ProxyManager\Configuration;
use ProxyManager\Factory\LazyLoadingValueHolderFactory;
use ProxyManager\GeneratorStrategy\EvaluatingGeneratorStrategy;

/**
 * Helper to create and configure a Container.
 *
 * With the default options, the container created is appropriate for the development environment.
 *
 * Example:
 *
 *     $builder = new ContainerBuilder();
 *     $container = $builder->build();
 *
 * @since  3.2
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class ContainerBuilder
{
    /**
     * Name of the container class, used to create the container.
     * @var string
     */
    private $containerClass;

    /**
     * @var boolean
     */
    private $useAutowiring = true;

    /**
     * @var boolean
     */
    private $useAnnotations = true;

    /**
     * @var boolean
     */
    private $ignorePhpDocErrors = false;

    /**
     * @var Cache
     */
    private $cache;

    /**
     * If true, write the proxies to disk to improve performances.
     * @var boolean
     */
    private $writeProxiesToFile = false;

    /**
     * Directory where to write the proxies (if $writeProxiesToFile is enabled).
     * @var string
     */
    private $proxyDirectory;

    /**
     * If PHP-DI is wrapped in another container, this references the wrapper.
     * @var ContainerInteropInterface
     */
    private $wrapperContainer;

    /**
     * Files of definitions for the container.
     * @var string[]
     */
    private $files = array();

    /**
     * Build a container configured for the dev environment.
     *
     * @return Container
     */
    public static function buildDevContainer()
    {
        $builder = new self();
        return $builder->build();
    }

    /**
     * @param string $containerClass Name of the container class, used to create the container.
     */
    public function __construct($containerClass = 'DI\Container')
    {
        $this->containerClass = $containerClass;
    }

    /**
     * Build and return a container.
     *
     * @return Container
     */
    public function build()
    {
        // Definition sources
        $firstSource = null;
        $lastSource = null;
        foreach (array_reverse($this->files) as $file) {
            $source = new PHPFileDefinitionSource($file);
            // Chain file sources
            if ($lastSource instanceof PHPFileDefinitionSource) {
                $lastSource->chain($source);
            } else {
                $firstSource = $source;
            }
            $lastSource = $source;
        }
        if ($this->useAnnotations) {
            if ($lastSource) {
                $lastSource->chain(new AnnotationDefinitionSource($this->ignorePhpDocErrors));
            } else {
                $firstSource = new AnnotationDefinitionSource($this->ignorePhpDocErrors);
            }
        } elseif ($this->useAutowiring) {
            if ($lastSource) {
                $lastSource->chain(new ReflectionDefinitionSource());
            } else {
                $firstSource = new ReflectionDefinitionSource();
            }
        }

        // Definition manager
        $definitionManager = new DefinitionManager($firstSource);
        if ($this->cache) {
            $definitionManager->setCache($this->cache);
        }

        // Proxy factory
        $proxyFactory = $this->buildProxyFactory();

        $containerClass = $this->containerClass;

        return new $containerClass($definitionManager, $proxyFactory, $this->wrapperContainer);
    }

    /**
     * Enable or disable the use of autowiring to guess injections.
     *
     * By default, enabled.
     *
     * @param boolean $bool
     * @return ContainerBuilder
     */
    public function useAutowiring($bool)
    {
        $this->useAutowiring = $bool;
        return $this;
    }

    /**
     * Enable or disable the use of annotations to guess injections.
     *
     * By default, enabled.
     *
     * @param boolean $bool
     * @return ContainerBuilder
     */
    public function useAnnotations($bool)
    {
        $this->useAnnotations = $bool;
        return $this;
    }

    /**
     * Enable or disable ignoring phpdoc errors (non-existent classes in `@param` or `@var`)
     * 
     * @param boolean $bool
     * @return ContainerBuilder
     */
    public function ignorePhpDocErrors($bool)
    {
        $this->ignorePhpDocErrors = $bool;
        return $this;
    }

    /**
     * Enables the use of a cache for the definitions.
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
     * Configure the proxy generation
     *
     * For dev environment, use writeProxiesToFile(false) (default configuration)
     * For production environment, use writeProxiesToFile(true, 'tmp/proxies')
     *
     * @param boolean     $writeToFile    If true, write the proxies to disk to improve performances
     * @param string|null $proxyDirectory Directory where to write the proxies
     * @return ContainerBuilder
     *
     * @throws InvalidArgumentException when writeToFile is set to true and the proxy directory is null
     */
    public function writeProxiesToFile($writeToFile, $proxyDirectory = null)
    {
        $this->writeProxiesToFile = $writeToFile;

        if ($writeToFile && $proxyDirectory === null) {
            throw new InvalidArgumentException(
                "The proxy directory must be specified if you want to write proxies on disk"
            );
        }
        $this->proxyDirectory = $proxyDirectory;

        return $this;
    }

    /**
     * If PHP-DI's container is wrapped by another container, we can
     * set this so that PHP-DI will use the wrapper rather than itself for building objects.
     *
     * @param ContainerInteropInterface $otherContainer
     * @return $this
     */
    public function wrapContainer(ContainerInteropInterface $otherContainer)
    {
        $this->wrapperContainer = $otherContainer;

        return $this;
    }

    /**
     * Add a file containing definitions to the container.
     *
     * @param string $file
     */
    public function addDefinitions($file)
    {
        $this->files[] = $file;
    }

    /**
     * @return LazyLoadingValueHolderFactory
     */
    private function buildProxyFactory()
    {
        $config = new Configuration();
        // TODO useless since ProxyManager 0.5, line kept for compatibility with previous versions
        $config->setAutoGenerateProxies(true);

        if ($this->writeProxiesToFile) {
            $config->setProxiesTargetDir($this->proxyDirectory);
            spl_autoload_register($config->getProxyAutoloader());
        } else {
            $config->setGeneratorStrategy(new EvaluatingGeneratorStrategy());
        }

        return new LazyLoadingValueHolderFactory($config);
    }
}
