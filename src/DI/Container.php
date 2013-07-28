<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.io/PHP-DI/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI;

use Closure;
use DI\Definition\ClassDefinition;
use DI\Definition\ClosureDefinition;
use DI\Definition\DefinitionManager;
use DI\Definition\Helper\ClassDefinitionHelper;
use DI\Definition\ValueDefinition;
use DI\Definition\FileLoader\DefinitionFileLoader;
use Doctrine\Common\Cache\Cache;
use Exception;
use InvalidArgumentException;
use ProxyManager\Factory\LazyLoadingValueHolderFactory;
use ProxyManager\GeneratorStrategy\EvaluatingGeneratorStrategy;

/**
 * Dependency Injection Container
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class Container
{

    /**
     * Map of instances of entry with Singleton scope
     * @var array
     */
    private $entries = array();

    /**
     * @var DefinitionManager
     */
    private $definitionManager;

    /**
     * @var FactoryInterface
     */
    private $factory;

    /**
     * @var LazyLoadingValueHolderFactory
     */
    private $proxyFactory;

    /**
     * Array of classes being instantiated.
     * Used to avoid circular dependencies.
     * @var array
     */
    private $classesBeingInstantiated = array();

    /**
     * @param DefinitionManager|null             $definitionManager
     * @param FactoryInterface|null              $factory
     * @param LazyLoadingValueHolderFactory|null $proxyFactory
     */
    public function __construct(
        DefinitionManager $definitionManager = null,
        FactoryInterface $factory = null,
        LazyLoadingValueHolderFactory $proxyFactory = null
    ) {
        $this->definitionManager = $definitionManager ?: $this->createDefaultDefinitionManager();
        $this->factory = $factory ?: $this->createDefaultFactory();
        $this->proxyFactory = $proxyFactory ?: $this->createDefaultProxyFactory();

        // Auto-register the container
        $this->entries[get_class($this)] = $this;
    }

    /**
     * Returns an instance by its name
     *
     * @param string $name Entry name or a class name
     * @param bool   $useProxy If true, returns a proxy class of the instance
     *                         if it is not already loaded
     * @throws InvalidArgumentException
     * @throws DependencyException
     * @throws NotFoundException
     * @return mixed Instance
     */
    public function get($name, $useProxy = false)
    {
        if (!is_string($name)) {
            throw new InvalidArgumentException("The name parameter must be of type string");
        }

        // Try to find the entry in the map
        if (array_key_exists($name, $this->entries)) {
            $entry = $this->entries[$name];
            // If it's a closure, resolve it and save the result
            if ($entry instanceof \Closure) {
                $entry = $entry($this);
                $this->entries[$name] = $entry;
            }
            return $entry;
        }

        // Entry not loaded, use the definitions
        $definition = $this->definitionManager->getDefinition($name);

        // It's a value
        if ($definition instanceof ValueDefinition) {
            $this->entries[$name] = $definition->getValue();
            return $this->entries[$name];
        }

        // It's a closure
        if ($definition instanceof ClosureDefinition) {
            $this->entries[$name] = $definition->getValue($this);
            return $this->entries[$name];
        }

        // It's a class
        if ($definition instanceof ClassDefinition) {
            // Return a proxy class
            if ($useProxy) {
                return $this->getProxy($definition->getClassName());
            }

            // Create the instance
            $instance = $this->getNewInstance($definition);

            if ($definition->getScope() == Scope::SINGLETON()) {
                // If it's a singleton, store the newly created instance
                $this->entries[$name] = $instance;
            }

            return $instance;
        }

        throw new NotFoundException("No entry or class found for '$name'");
    }

    /**
     * Performs injection on an existing instance
     *
     * @param object $instance Object to perform injection upon
     * @throws InvalidArgumentException
     * @throws DependencyException
     * @return object $instance
     */
    public function injectAll($instance)
    {
        $definition = $this->definitionManager->getDefinition(get_class($instance));

        // Check that the definition is a class definition
        if ($definition instanceof ClassDefinition) {
            $instance = $this->factory->injectInstance($definition, $instance);
        }

        return $instance;
    }

    /**
     * Define an object or a value in the container
     *
     * @param string             $name Entry name
     * @param mixed|Closure|null $value Value, Closure or if *not set*, returns a ClassDefinitionHelper
     * If a null value is explicitly given ($container->set('foo', null)) then the null value is registered
     * and no ClassDefinitionHelper is returned.
     * Use $container->set('foo') to get a ClassDefinitionHelper.
     *
     * @return null|ClassDefinitionHelper
     */
    public function set($name, $value = null)
    {
        // Class definition
        if ($value === null) {
            // If a null value was explicitly given in the method call, then we register a null value
            if (func_num_args() === 2) {
                $definition = new ValueDefinition($name, $value);
                $this->definitionManager->addDefinition($definition);
                return null;
            }

            $helper = new ClassDefinitionHelper($name);
            $this->definitionManager->addDefinition($helper->getDefinition());
            return $helper;
        }

        // Closure definition
        if ($value instanceof Closure) {
            $definition = new ClosureDefinition($name, $value);
            $this->definitionManager->addDefinition($definition);
            return null;
        }

        // Value definition
        $definition = new ValueDefinition($name, $value);
        $this->definitionManager->addDefinition($definition);
        return null;
    }

    /**
     * Enable or disable the use of reflection
     *
     * @param boolean $bool
     * @deprecated Use ContainerBuilder::useReflection instead. Will be removed in next major release (v4).
     * @see ContainerBuilder::useReflection
     */
    public function useReflection($bool)
    {
        $this->definitionManager->useReflection($bool);
    }

    /**
     * Enable or disable the use of annotations
     *
     * @param boolean $bool
     * @deprecated Use ContainerBuilder::useAnnotations instead. Will be removed in next major release (v4).
     * @see ContainerBuilder::useAnnotations
     */
    public function useAnnotations($bool)
    {
        $this->definitionManager->useAnnotations($bool);
    }

    /**
     * Add definitions from an array
     *
     * @param array $definitions
     */
    public function addDefinitions(array $definitions)
    {
        $this->definitionManager->addArrayDefinitions($definitions);
    }

    /**
     * Add definitions contained in a file
     *
     * @param \DI\Definition\FileLoader\DefinitionFileLoader $definitionFileLoader
     * @throws \InvalidArgumentException
     */
    public function addDefinitionsFromFile(DefinitionFileLoader $definitionFileLoader)
    {
        $this->definitionManager->addDefinitionsFromFile($definitionFileLoader);
    }

    /**
     * Enables the use of a cache for the definitions
     *
     * @param Cache $cache Cache backend to use
     * @deprecated Use ContainerBuilder::setDefinitionCache instead. Will be removed in next major release (v4).
     * @see ContainerBuilder::setDefinitionCache
     */
    public function setDefinitionCache(Cache $cache)
    {
        $this->definitionManager->setCache($cache);
    }

    /**
     * Enables/disables the validation of the definitions
     *
     * By default, disabled
     * @param bool $bool
     * @deprecated Use ContainerBuilder::setDefinitionsValidation instead. Will be removed in next major release (v4).
     * @see ContainerBuilder::setDefinitionsValidation
     */
    public function setDefinitionsValidation($bool)
    {
        $this->definitionManager->setDefinitionsValidation($bool);
    }

    /**
     * @param FactoryInterface $factory
     */
    public function setFactory(FactoryInterface $factory)
    {
        $this->factory = $factory;
    }

    /**
     * @return FactoryInterface
     */
    public function getFactory()
    {
        return $this->factory;
    }

    /**
     * @return LazyLoadingValueHolderFactory
     */
    public function getProxyFactory()
    {
        return $this->proxyFactory;
    }

    /**
     * @param LazyLoadingValueHolderFactory $proxyFactory
     */
    public function setProxyFactory(LazyLoadingValueHolderFactory $proxyFactory)
    {
        $this->proxyFactory = $proxyFactory;
    }

    /**
     * @return DefinitionManager
     */
    public function getDefinitionManager()
    {
        return $this->definitionManager;
    }

    /**
     * @param ClassDefinition $classDefinition
     * @return object The instance
     */
    private function getNewInstance(ClassDefinition $classDefinition)
    {
        $classname = $classDefinition->getClassName();

        if (isset($this->classesBeingInstantiated[$classname])) {
            throw new DependencyException("Circular dependency detected while trying to instantiate class '$classname'");
        }
        $this->classesBeingInstantiated[$classname] = true;

        try {
            $instance = $this->factory->createInstance($classDefinition);
        } catch (Exception $exception) {
            unset($this->classesBeingInstantiated[$classname]);
            throw $exception;
        }

        unset($this->classesBeingInstantiated[$classname]);
        return $instance;
    }

    /**
     * Returns a proxy instance
     *
     * @param string $classname
     * @return object Proxy instance
     */
    private function getProxy($classname)
    {
        $container = $this;

        $proxy = $this->proxyFactory->createProxy(
            $classname,
            function (& $wrappedObject, $proxy, $method, $parameters, & $initializer) use ($container, $classname) {
                $wrappedObject = $container->get($classname);
                $initializer = null; // turning off further lazy initialization
                return true;
            }
        );

        return $proxy;
    }

    /**
     * @return DefinitionManager
     */
    private function createDefaultDefinitionManager()
    {
        $definitionManager = new DefinitionManager();
        $definitionManager->useReflection(true);
        $definitionManager->useAnnotations(true);

        return $definitionManager;
    }

    /**
     * @return FactoryInterface
     */
    private function createDefaultFactory()
    {
        return new Factory($this);
    }

    /**
     * @return LazyLoadingValueHolderFactory
     */
    private function createDefaultProxyFactory()
    {
        // Proxy factory
        $config = new \ProxyManager\Configuration();
        // By default, auto-generate proxies and don't write them to file
        $config->setAutoGenerateProxies(true);
        $config->setGeneratorStrategy(new EvaluatingGeneratorStrategy());

        return new LazyLoadingValueHolderFactory($config);
    }

}
