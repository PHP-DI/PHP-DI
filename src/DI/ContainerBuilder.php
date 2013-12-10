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
use DI\Definition\Source\DefinitionSource;
use Doctrine\Common\Cache\Cache;
use Interop\DI\ReadableContainerInterface;
use InvalidArgumentException;
use ProxyManager\Configuration;
use ProxyManager\Factory\LazyLoadingValueHolderFactory;
use ProxyManager\GeneratorStrategy\EvaluatingGeneratorStrategy;

/**
 * Helper to create and configure a Container.
 *
 * With the default options, the container created is appropriate for the development environment.
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
    private $useReflection = true;

    /**
     * @var boolean
     */
    private $useAnnotations = true;

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
     * @var ReadableContainerInterface
     */
    private $wrapperContainer;

    /**
     * Source of definitions for the container.
     * @var DefinitionSource[]
     */
    private $definitionSources = array();

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
     * @return Container
     */
    public function build()
    {
        // Definition manager
        $definitionManager = new DefinitionManager($this->useReflection, $this->useAnnotations);
        if ($this->cache) {
            $definitionManager->setCache($this->cache);
        }
        foreach ($this->definitionSources as $definitionSource) {
            $definitionManager->addDefinitionSource($definitionSource);
        }

        // Proxy factory
        $proxyFactory = $this->buildProxyFactory();

        $containerClass = $this->containerClass;

        return new $containerClass($definitionManager, $proxyFactory, $this->wrapperContainer);
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
     * @param ReadableContainerInterface $otherContainer
     * @return $this
     */
    public function wrapContainer(ReadableContainerInterface $otherContainer)
    {
        $this->wrapperContainer = $otherContainer;

        return $this;
    }

    /**
     * Add definitions to the container by adding a source of definitions.
     *
     * Do not add ReflectionDefinitionSource or AnnotationDefinitionSource manually, they should be
     * handled with useReflection() and useAnnotations().
     *
     * @param DefinitionSource $definitionSource
     */
    public function addDefinitions(DefinitionSource $definitionSource)
    {
        $this->definitionSources[] = $definitionSource;
    }

    /**
     * @return LazyLoadingValueHolderFactory
     */
    private function buildProxyFactory()
    {
        $config = new Configuration();
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
