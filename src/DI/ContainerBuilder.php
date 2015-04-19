<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI;

use DI\Definition\Source\AnnotationReader;
use DI\Definition\Source\DefinitionArray;
use DI\Definition\Source\CachedDefinitionSource;
use DI\Definition\Source\DefinitionSource;
use DI\Definition\Source\DefinitionFile;
use DI\Definition\Source\Autowiring;
use DI\Definition\Source\SourceChain;
use DI\Proxy\ProxyFactory;
use Doctrine\Common\Cache\Cache;
use Interop\Container\ContainerInterface;
use InvalidArgumentException;

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
    private $useAnnotations = false;

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
     * @var ContainerInterface
     */
    private $wrapperContainer;

    /**
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
     * Build and return a container.
     *
     * @return Container
     */
    public function build()
    {
        $sources = array_reverse($this->definitionSources);
        if ($this->useAnnotations) {
            $sources[] = new AnnotationReader($this->ignorePhpDocErrors);
        } elseif ($this->useAutowiring) {
            $sources[] = new Autowiring();
        }

        $chain = new SourceChain($sources);

        if ($this->cache) {
            $source = new CachedDefinitionSource($chain, $this->cache);
            $chain->setRootDefinitionSource($source);
        } else {
            $source = $chain;
            // Mutable definition source
            $source->setMutableDefinitionSource(new DefinitionArray());
        }

        $proxyFactory = new ProxyFactory($this->writeProxiesToFile, $this->proxyDirectory);

        $containerClass = $this->containerClass;

        return new $containerClass($source, $proxyFactory, $this->wrapperContainer);
    }

    /**
     * Enable or disable the use of autowiring to guess injections.
     *
     * Enabled by default.
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
     * Disabled by default.
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
     * @param ContainerInterface $otherContainer
     * @return $this
     */
    public function wrapContainer(ContainerInterface $otherContainer)
    {
        $this->wrapperContainer = $otherContainer;

        return $this;
    }

    /**
     * Add definitions to the container.
     *
     * @param string|array|DefinitionSource $definitions Can be an array of definitions, the
     *                                                   name of a file containing definitions
     *                                                   or a DefinitionSource object.
     * @return $this
     */
    public function addDefinitions($definitions)
    {
        if (is_string($definitions)) {
            // File
            $definitions = new DefinitionFile($definitions);
        } elseif (is_array($definitions)) {
            $definitions = new DefinitionArray($definitions);
        } elseif (! $definitions instanceof DefinitionSource) {
            throw new InvalidArgumentException(sprintf(
                '%s parameter must be a string, an array or a DefinitionSource object, %s given',
                'ContainerBuilder::addDefinitions()',
                is_object($definitions) ? get_class($definitions) : gettype($definitions)
            ));
        }

        $this->definitionSources[] = $definitions;

        return $this;
    }
}
