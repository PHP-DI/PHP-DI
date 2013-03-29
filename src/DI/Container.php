<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.com/PHP-DI/
 * @copyright 2012 Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI;

use ArrayAccess;
use DI\Definition\AnnotationException;
use DI\Definition\ClassDefinition;
use DI\Definition\ClosureDefinition;
use DI\Definition\DefinitionException;
use DI\Definition\DefinitionReader;
use DI\Definition\MethodInjection;
use DI\Definition\PropertyInjection;
use DI\Definition\ValueDefinition;
use DI\Proxy\Proxy;
use ReflectionClass;
use ReflectionProperty;

/**
 * Container
 *
 * This class uses the resettable Singleton pattern (resettable for the tests).
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class Container implements ArrayAccess
{

    private static $singletonInstance = null;

    /**
     * Map of instances and values that can be injected
     * @var array
     */
    private $entries = array();

    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * Array of classes being instantiated.
     * Used to avoid circular dependencies.
     * @var array
     */
    private $classesBeingInstantiated = array();

    /**
     * Returns an instance of the class (Singleton design pattern)
     * @return \DI\Container
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
        $this->configuration = new Configuration();
        $this->configuration->useReflection(true);
        $this->configuration->useAnnotations(true);
    }

    /**
     * @return Configuration
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     * Returns an instance by its name
     *
     * @param string $name Can be a bean name or a class name
     * @param bool   $useProxy If true, returns a proxy class of the instance
     *                            if it is not already loaded
     * @throws \InvalidArgumentException
     * @throws DependencyException
     * @throws NotFoundException
     * @return mixed Instance
     */
    public function get($name, $useProxy = false)
    {
        if (!is_string($name)) {
            throw new \InvalidArgumentException("The name parameter must be of type string");
        }

        // Try to find the entry in the map
        if (array_key_exists($name, $this->entries)) {
            $entry = $this->entries[$name];
            // If it's a closure, resolve it and save the bean
            if ($entry instanceof \Closure) {
                $entry = $entry($this);
                $this->entries[$name] = $entry;
            }
            return $entry;
        }

        // Entry not loaded, use the definitions
        $definition = $this->getDefinitionReader()->getDefinition($name);

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
            // TODO refactor
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

        throw new NotFoundException("No bean, value or class found for '$name'");
    }

    /**
     * Define a bean or a value in the container
     *
     * @param string $name Name to use with Inject annotation
     * @param mixed  $entry Entry to store in the container (bean or value)
     */
    public function set($name, $entry)
    {
        $this->entries[$name] = $entry;
    }

    /**
     * Inject the dependencies of the object (marked with the Inject annotation)
     *
     * @param mixed $object Object in which to resolve dependencies
     * @throws Definition\AnnotationException
     * @throws DependencyException
     * @todo Make private
     * @deprecated (until private)
     */
    public function injectAll($object)
    {
        if (is_null($object)) {
            throw new DependencyException("null given, object instance expected");
        }
        if (!is_object($object)) {
            throw new DependencyException("object instance expected");
        }

        // Get the class definition
        /** @var $classDefinition ClassDefinition */
        $classDefinition = $this->getDefinitionReader()->getDefinition(get_class($object));

        if ($classDefinition === null) {
            return;
        }

        // Process annotations on methods
        foreach ($classDefinition->getMethodInjections() as $methodInjection) {
            $this->injectMethod($object, $methodInjection);
        }

        // Process annotations on properties
        foreach ($classDefinition->getPropertyInjections() as $propertyInjection) {
            $this->injectProperty($object, $propertyInjection);
        }
    }

    /**
     * @return DefinitionReader The definition reader
     */
    public function getDefinitionReader()
    {
        return $this->configuration->getDefinitionReader();
    }

    /**
     * Whether an offset exists
     *
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset An offset to check for
     * @return boolean true on success or false on failure
     */
    public function offsetExists($offset)
    {
        // Try to find the entry in the map
        if (array_key_exists($offset, $this->entries)) {
            return true;
        }
        // Entry not found, it's a class name
        if (class_exists($offset)) {
            return true;
        }
        return false;
    }

    /**
     * Offset to retrieve
     *
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset The offset to retrieve
     * @return mixed Can return all value types
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * Offset to set
     *
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset The offset to assign the value to
     * @param mixed $value The value to set
     */
    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }

    /**
     * Offset to unset
     *
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset The offset to unset
     */
    public function offsetUnset($offset)
    {
        unset($this->entries[$offset]);
    }

    /**
     * Returns a proxy class
     *
     * @param string $classname
     * @return \DI\Proxy\Proxy Proxy instance
     */
    private function getProxy($classname)
    {
        $container = $this;
        return new Proxy(function () use ($container, $classname) {
            return $container->get($classname);
        });
    }

    /**
     * Create a new instance of the class
     *
     * @param ClassDefinition $classDefinition
     * @throws DependencyException
     * @throws DefinitionException
     * @return object the instance
     */
    private function getNewInstance(ClassDefinition $classDefinition)
    {
        $classname = $classDefinition->getClassName();

        if (isset($this->classesBeingInstantiated[$classname])) {
            throw new DependencyException("Circular dependency detected while trying to instantiate class '$classname'");
        }
        $this->classesBeingInstantiated[$classname] = true;

        try {
            $classReflection = new ReflectionClass($classname);

            if (!$classReflection->isInstantiable()) {
                throw new DependencyException($classReflection->name . " is not instantiable");
            }

            $instance = $this->newInstanceWithoutConstructor($classReflection);

            // Inject the dependencies
            // TODO inject properties, constructor, and then methods
            try {
                $this->injectAll($instance);
            } catch (DependencyException $e) {
                throw $e;
            } catch (DefinitionException $e) {
                throw $e;
            } catch (\Exception $e) {
                throw new DependencyException("Error while injecting dependencies into $classname: " . $e->getMessage(), 0, $e);
            }

            // Constructor injection
            $this->injectConstructor($instance, $classReflection, $classDefinition->getConstructorInjection());
        } catch (\Exception $exception) {
            unset($this->classesBeingInstantiated[$classname]);
            throw $exception;
        }

        unset($this->classesBeingInstantiated[$classname]);
        return $instance;
    }

    /**
     * Creates a new instance of a class without calling its constructor
     *
     * @param ReflectionClass $classReflection
     * @return mixed|void
     */
    private function newInstanceWithoutConstructor(ReflectionClass $classReflection)
    {
        if (version_compare(PHP_VERSION, '5.4.0') >= 0) {
            // Create a new class instance without calling the constructor (PHP 5.4 magic)
            return $classReflection->newInstanceWithoutConstructor();
        } else {
            $classname = $classReflection->name;
            return unserialize(
                sprintf(
                    'O:%d:"%s":0:{}',
                    strlen($classname),
                    $classname
                )
            );
        }
    }

    /**
     * Inject dependencies through the constructor
     * @param mixed                $object
     * @param ReflectionClass      $classReflection
     * @param MethodInjection|null $constructorInjection
     * @throws DefinitionException
     */
    private function injectConstructor($object, ReflectionClass $classReflection, MethodInjection $constructorInjection = null)
    {
        $constructorReflection = $classReflection->getConstructor();

        // No constructor
        if (! $constructorReflection) {
            return;
        }

        // Check the definition and the class parameter number match
        $nbRequiredParameters = $constructorReflection->getNumberOfRequiredParameters();
        $parameterInjections = $constructorInjection ? $constructorInjection->getParameterInjections() : array();
        if (count($parameterInjections) < $nbRequiredParameters) {
            throw new DefinitionException("The constructor of {$classReflection->name} takes $nbRequiredParameters parameters, "
                . count($parameterInjections) . " defined or guessed");
        }

        if (count($parameterInjections) === 0) {
            $constructorReflection->invoke($object);
            return;
        }

        $args = array();
        foreach ($parameterInjections as $parameterInjection) {
            $entryName = $parameterInjection->getEntryName();
            if ($entryName === null) {
                throw new DefinitionException("The parameter '" . $parameterInjection->getParameterName()
                    . "' of the constructor of '{$classReflection->name}' has no type defined or guessable");
            }

            $args[] = $this->get($entryName);
        }

        $constructorReflection->invokeArgs($object, $args);
    }

    /**
     * Resolve the Inject annotation on a method
     * @param mixed           $object Object to inject dependencies to
     * @param MethodInjection $methodInjection
     * @throws DependencyException
     * @throws DefinitionException
     */
    private function injectMethod($object, MethodInjection $methodInjection)
    {
        $methodName = $methodInjection->getMethodName();
        $classReflection = new ReflectionClass($object);
        $methodReflection = $classReflection->getMethod($methodName);

        // Check the definition and the class parameter number match
        $nbRequiredParameters = $methodReflection->getNumberOfRequiredParameters();
        $parameterInjections = $methodInjection ? $methodInjection->getParameterInjections() : array();
        if (count($parameterInjections) < $nbRequiredParameters) {
            throw new DefinitionException("{$classReflection->name}::$methodName takes $nbRequiredParameters parameters, "
                . count($parameterInjections) . " defined or guessed");
        }

        // No parameters
        if (count($parameterInjections) === 0) {
            $methodReflection->invoke($object);
            return;
        }

        $args = array();
        foreach ($parameterInjections as $parameterInjection) {
            $entryName = $parameterInjection->getEntryName();
            if ($entryName === null) {
                throw new DefinitionException("The parameter '" . $parameterInjection->getParameterName()
                    . "' of {$classReflection->name}::$methodName has no type defined or guessable");
            }

            $args[] = $this->get($entryName);
        }

        $methodReflection->invokeArgs($object, $args);
    }

    /**
     * Resolve the Inject annotation on a property
     * @param mixed             $object            Object to inject dependencies to
     * @param PropertyInjection $propertyInjection Property injection definition
     * @throws DependencyException
     * @throws DefinitionException
     */
    private function injectProperty($object, PropertyInjection $propertyInjection)
    {
        $propertyName = $propertyInjection->getPropertyName();
        $property = new ReflectionProperty(get_class($object), $propertyName);
        // Allow access to protected and private properties
        $property->setAccessible(true);
        // Consider only not set properties
        if ($property->getValue($object) !== null) {
            return;
        }

        $entryName = $propertyInjection->getEntryName();
        if ($entryName === null) {
            throw new DefinitionException(get_class($object) . "::$propertyName has no type defined or guessable");
        }

        // Get the dependency
        try {
            $value = $this->get($propertyInjection->getEntryName(), $propertyInjection->isLazy());
        } catch (DependencyException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new DependencyException("Error while injecting $propertyName in "
                . get_class($object) . "::" . $property->name . ". " . $e->getMessage(), 0, $e);
        }
        // Inject the dependency
        $property->setValue($object, $value);
    }

    private final function __clone()
    {
    }

}
