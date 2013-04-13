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
use DI\Definition\Helper\ClassDefinitionHelper;
use DI\Definition\Source\DefinitionSource;
use DI\Definition\ValueDefinition;
use DI\Proxy\Proxy;
use Exception;
use InvalidArgumentException;

/**
 * Dependency Injection Container
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class Container
{

    /**
     * Singleton instance
     * @var self
     */
    private static $singletonInstance = null;

    /**
     * Map of instances of entry with Singleton scope
     * @var array
     */
    private $entries = array();

    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @var FactoryInterface
     */
    private $factory;

    /**
     * Array of classes being instantiated.
     * Used to avoid circular dependencies.
     * @var array
     */
    private $classesBeingInstantiated = array();

    /**
     * Returns an instance of the class (Singleton design pattern)
     * The constructor is left public to allow usage as singleton or as new instance
     * @return Container
     */
    public static function getInstance()
    {
        if (self::$singletonInstance == null) {
            self::$singletonInstance = new self();
        }
        return self::$singletonInstance;
    }

    /**
     * Reset the singleton instance, for the tests only
     */
    public static function reset()
    {
        self::$singletonInstance = null;
    }

    /**
     * Constructor creates a default configuration
     */
    public function __construct()
    {
        // Default configuration
        $this->configuration = new Configuration();
        $this->configuration->useReflection(true);
        $this->configuration->useAnnotations(true);

        // Default factory
        $this->factory = new Factory($this);
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
        $definition = $this->getDefinitionSource()->getDefinition($name);

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
     * Define an object or a value in the container
     *
     * @param string             $name Entry name
     * @param mixed|Closure|null $value Value, Closure or if null, returns a ClassDefinitionHelper
     *
     * @return null|ClassDefinitionHelper
     */
    public function set($name, $value = null)
    {
        // Class definition
        if ($value === null) {
            $helper = new ClassDefinitionHelper($name);
            $this->configuration->addDefinition($helper->getDefinition());
            return $helper;
        }

        // Closure definition
        if ($value instanceof Closure) {
            $definition = new ClosureDefinition($name, $value);
            $this->configuration->addDefinition($definition);
            return null;
        }

        // Value definition
        $definition = new ValueDefinition($name, $value);
        $this->configuration->addDefinition($definition);
        return null;
    }

    /**
     * Enable or disable the use of reflection
     *
     * @param boolean $bool
     */
    public function useReflection($bool)
    {
        $this->configuration->useReflection($bool);
    }

    /**
     * Enable or disable the use of annotations
     *
     * @param boolean $bool
     */
    public function useAnnotations($bool)
    {
        $this->configuration->useAnnotations($bool);
    }

    /**
     * Add definitions from an array
     *
     * @param array $definitions
     */
    public function addDefinitions(array $definitions)
    {
        $this->configuration->addArrayDefinitions($definitions);
    }

    /**
     * @return DefinitionSource The definition source
     */
    public function getDefinitionSource()
    {
        return $this->configuration->getDefinitionSource();
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
     * Returns a proxy class
     *
     * @param string $classname
     * @return Proxy Proxy instance
     */
    private function getProxy($classname)
    {
        $container = $this;
        return new Proxy(function () use ($container, $classname) {
            return $container->get($classname);
        });
    }

}
