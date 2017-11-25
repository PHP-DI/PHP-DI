<?php

declare(strict_types=1);

namespace DI;

use DI\Definition\Source\AnnotationBasedAutowiring;
use DI\Definition\Source\DefinitionArray;
use DI\Definition\Source\DefinitionFile;
use DI\Definition\Source\DefinitionSource;
use DI\Definition\Source\NoAutowiring;
use DI\Definition\Source\ReflectionBasedAutowiring;
use DI\Definition\Source\SourceChain;
use DI\Proxy\ProxyFactory;
use InvalidArgumentException;
use Psr\Container\ContainerInterface;

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
 * @api
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
     * Name of the container parent class, used on compiled container.
     * @var string
     */
    private $containerParentClass;

    /**
     * @var bool
     */
    private $useAutowiring = true;

    /**
     * @var bool
     */
    private $useAnnotations = false;

    /**
     * @var bool
     */
    private $ignorePhpDocErrors = false;

    /**
     * If true, write the proxies to disk to improve performances.
     * @var bool
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
     * @var DefinitionSource[]|string[]|array[]
     */
    private $definitionSources = [];

    /**
     * Whether the container has already been built.
     * @var bool
     */
    private $locked = false;

    /**
     * @var string|null
     */
    private $compileToDirectory;

    /**
     * Build a container configured for the dev environment.
     */
    public static function buildDevContainer() : Container
    {
        return new Container;
    }

    /**
     * @param string $containerClass Name of the container class, used to create the container.
     */
    public function __construct(string $containerClass = 'DI\Container')
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
            $autowiring = new AnnotationBasedAutowiring($this->ignorePhpDocErrors);
            $sources[] = $autowiring;
        } elseif ($this->useAutowiring) {
            $autowiring = new ReflectionBasedAutowiring;
            $sources[] = $autowiring;
        } else {
            $autowiring = new NoAutowiring;
        }

        $sources = array_map(function ($definitions) use ($autowiring) {
            if (is_string($definitions)) {
                // File
                return new DefinitionFile($definitions, $autowiring);
            } elseif (is_array($definitions)) {
                return new DefinitionArray($definitions, $autowiring);
            }

            return $definitions;
        }, $sources);
        $source = new SourceChain($sources);

        // Mutable definition source
        $source->setMutableDefinitionSource(new DefinitionArray([], $autowiring));

        $proxyFactory = new ProxyFactory($this->writeProxiesToFile, $this->proxyDirectory);

        $this->locked = true;

        $containerClass = $this->containerClass;

        if ($this->compileToDirectory) {
            $compiler = new Compiler;
            $compiledContainerFile = $compiler->compile(
                $source,
                $this->compileToDirectory,
                $containerClass,
                $this->containerParentClass,
                $this->useAutowiring || $this->useAnnotations
            );
            // Only load the file if it hasn't been already loaded
            // (the container can be created multiple times in the same process)
            if (!class_exists($containerClass, false)) {
                require $compiledContainerFile;
            }
        }

        return new $containerClass($source, $proxyFactory, $this->wrapperContainer);
    }

    /**
     * Compile the container for optimum performances.
     *
     * Be aware that the container is compiled once and never updated!
     *
     * Therefore:
     *
     * - in production you should clear that directory every time you deploy
     * - in development you should not compile the container
     *
     * @see http://php-di.org/doc/performances.html
     *
     * @param string $directory Directory in which to put the compiled container.
     * @param string $containerClass Name of the compiled class. Customize only if necessary.
     * @param string $containerParentClass Name of the compiled container parent class. Customize only if necessary.
     */
    public function enableCompilation(
        string $directory,
        string $containerClass = 'CompiledContainer',
        string $containerParentClass = CompiledContainer::class
    ) : ContainerBuilder {
        $this->ensureNotLocked();

        $this->compileToDirectory = $directory;
        $this->containerClass = $containerClass;
        $this->containerParentClass = $containerParentClass;

        return $this;
    }

    /**
     * Enable or disable the use of autowiring to guess injections.
     *
     * Enabled by default.
     *
     * @return $this
     */
    public function useAutowiring(bool $bool) : ContainerBuilder
    {
        $this->ensureNotLocked();

        $this->useAutowiring = $bool;

        return $this;
    }

    /**
     * Enable or disable the use of annotations to guess injections.
     *
     * Disabled by default.
     *
     * @return $this
     */
    public function useAnnotations(bool $bool) : ContainerBuilder
    {
        $this->ensureNotLocked();

        $this->useAnnotations = $bool;

        return $this;
    }

    /**
     * Enable or disable ignoring phpdoc errors (non-existent classes in `@param` or `@var`).
     *
     * @return $this
     */
    public function ignorePhpDocErrors(bool $bool) : ContainerBuilder
    {
        $this->ensureNotLocked();

        $this->ignorePhpDocErrors = $bool;

        return $this;
    }

    /**
     * Configure the proxy generation.
     *
     * For dev environment, use `writeProxiesToFile(false)` (default configuration)
     * For production environment, use `writeProxiesToFile(true, 'tmp/proxies')`
     *
     * @see http://php-di.org/doc/lazy-injection.html
     *
     * @param bool $writeToFile If true, write the proxies to disk to improve performances
     * @param string|null $proxyDirectory Directory where to write the proxies
     * @throws InvalidArgumentException when writeToFile is set to true and the proxy directory is null
     * @return $this
     */
    public function writeProxiesToFile(bool $writeToFile, string $proxyDirectory = null) : ContainerBuilder
    {
        $this->ensureNotLocked();

        $this->writeProxiesToFile = $writeToFile;

        if ($writeToFile && $proxyDirectory === null) {
            throw new InvalidArgumentException(
                'The proxy directory must be specified if you want to write proxies on disk'
            );
        }
        $this->proxyDirectory = $proxyDirectory;

        return $this;
    }

    /**
     * If PHP-DI's container is wrapped by another container, we can
     * set this so that PHP-DI will use the wrapper rather than itself for building objects.
     *
     * @return $this
     */
    public function wrapContainer(ContainerInterface $otherContainer) : ContainerBuilder
    {
        $this->ensureNotLocked();

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
    public function addDefinitions($definitions) : ContainerBuilder
    {
        $this->ensureNotLocked();

        if (!is_string($definitions) && !is_array($definitions) && !($definitions instanceof DefinitionSource)) {
            throw new InvalidArgumentException(sprintf(
                '%s parameter must be a string, an array or a DefinitionSource object, %s given',
                'ContainerBuilder::addDefinitions()',
                is_object($definitions) ? get_class($definitions) : gettype($definitions)
            ));
        }

        $this->definitionSources[] = $definitions;

        return $this;
    }

    /**
     * Are we building a compiled container?
     */
    public function isCompiled() : bool
    {
        if(null === $this->compileToDirectory){
            return false;
        }

        if(class_exists($this->containerClass)){
            return true;
        }else{
            $fileName = rtrim($this->compileToDirectory, '/') . '/' . $this->containerClass . '.php';
            if (file_exists($fileName)) {
                return true;
            }
        }

        return false;
    }

    private function ensureNotLocked()
    {
        if ($this->locked) {
            throw new \LogicException('The ContainerBuilder cannot be modified after the container has been built');
        }
    }
}
