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
use DI\Definition\AnnotationDefinitionReader;
use DI\Definition\DefinitionReader;
use DI\Definition\MethodInjection;
use DI\Definition\PropertyInjection;
use DI\Proxy\Proxy;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;

/**
 * Container
 *
 * This class uses the resettable Singleton pattern (resettable for the tests).
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
     * @var DefinitionReader
     */
    private $definitionReader;

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
     * Applies the configuration given
     * @param array $configuration See the documentation
     */
    public static function addConfiguration(array $configuration)
    {
        $container = self::getInstance();
        // Entries
        if (isset($configuration['entries'])) {
            foreach ($configuration['entries'] as $name => $entry) {
                $container->set($name, $entry);
            }
        }
        // Aliases
        if (isset($configuration['aliases'])) {
            foreach ($configuration['aliases'] as $from => $to) {
                $container->set(
                    $from,
                    function (Container $c) use ($to) {
                        return $c->get($to);
                    }
                );
            }
        }
    }

    /**
     * Protected constructor because of singleton
     */
    protected function __construct()
    {
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
        // Entry not found, use the factory if it's a class name
        if (class_exists($name)) {
            // Return a proxy class
            if ($useProxy) {
                return $this->getProxy($name);
            }

            $scope = $this->getDefinitionReader()->getDefinition($name)->getScope();
            if ($scope == Scope::PROTOTYPE()) {
                return $this->getNewInstance($name);
            }

            // If it's a singleton, store the newly created instance
            $this->entries[$name] = $this->getNewInstance($name);
            return $this->entries[$name];
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
     * @throws Annotations\AnnotationException
     * @throws DependencyException
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
        $classDefinition = $this->getDefinitionReader()->getDefinition(get_class($object));

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
        if ($this->definitionReader == null) {
            $this->definitionReader = new AnnotationDefinitionReader();
        }
        return $this->definitionReader;
    }

    /**
     * @param DefinitionReader $definitionReader The definition reader
     */
    public function setDefinitionReader(DefinitionReader $definitionReader)
    {
        $this->definitionReader = $definitionReader;
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
     * @param string $classname Class to instantiate
     * @throws DependencyException
     * @throws \Exception
     * @return object the instance
     */
    private function getNewInstance($classname)
    {
        if (isset($this->classesBeingInstantiated[$classname])) {
            throw new DependencyException("Circular dependency detected while trying to instantiate class '$classname'");
        }
        $this->classesBeingInstantiated[$classname] = true;

        try {
            $classReflection = new ReflectionClass($classname);
            $constructorReflection = $classReflection->getConstructor();
            $instance = $this->newInstanceWithoutConstructor($classReflection);

            // Inject the dependencies
            $this->injectAll($instance);

            // Call the constructor
            if ($constructorReflection) {
                if ($constructorReflection->getNumberOfRequiredParameters() > 0) {
                    // Constructor injection
                    $this->injectConstructor($instance, $constructorReflection);
                } else {
                    $constructorReflection->invoke($instance);
                }
            }
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
     * @param mixed            $object
     * @param ReflectionMethod $constructorReflection
     * @throws AnnotationException
     */
    private function injectConstructor($object, ReflectionMethod $constructorReflection)
    {
        // TODO use Definition
        $args = array();
        foreach ($constructorReflection->getParameters() as $parameter) {
            $parameterClass = $parameter->getClass();
            if ($parameterClass === null) {
                throw new AnnotationException("The parameter '{$parameter->name}' of the constructor of '"
                    . get_class($object) . "' has no type: impossible to deduce its type");
            }
            $args[] = $this->get($parameterClass->name);
        }
        $constructorReflection->invokeArgs($object, $args);
    }

    /**
     * Resolve the Inject annotation on a method
     * @param mixed           $object Object to inject dependencies to
     * @param MethodInjection $methodInjection
     * @throws DependencyException
     * @throws NotFoundException
     */
    private function injectMethod($object, MethodInjection $methodInjection)
    {
        $classname = get_class($object);
        $methodName = $methodInjection->getMethodName();
        // One 1-parameter methods supported for now
        $parameterInjections = $methodInjection->getParameterInjections();
        $parameterInjection = $parameterInjections[0];
        $entryName = $parameterInjection->getEntryName();
        // Get the dependency
        try {
            $value = $this->get($entryName);
        } catch (NotFoundException $e) {
            // Better exception message
            throw new NotFoundException("@Inject was found on $classname::$methodName(...)"
                . " but no bean or value '$entryName' was found");
        } catch (DependencyException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new DependencyException("Error while injecting {$entryName} to $classname::$methodName(...). "
                . $e->getMessage(), 0, $e);
        }
        // Inject the dependency by calling the method
        call_user_func_array(array($object, $methodName), array($value));
    }

    /**
     * Resolve the Inject annotation on a property
     * @param mixed             $object Object to inject dependencies to
     * @param PropertyInjection $propertyInjection Property injection definition
     * @throws DependencyException
     * @throws NotFoundException
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
        // Get the dependency
        try {
            $value = $this->get($propertyInjection->getEntryName(), $propertyInjection->isLazy());
        } catch (NotFoundException $e) {
            // Better exception message
            throw new NotFoundException("@Inject was found on " . get_class($object) . "::" . $property->name
                . " but no bean or value '$propertyName' was found");
        } catch (DependencyException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new DependencyException("Error while injecting $propertyName in "
                . get_class($object) . "::" . $property->name . ". "
                . $e->getMessage(), 0, $e);
        }
        // Inject the dependency
        $property->setValue($object, $value);
    }

    private final function __clone()
    {
    }

}
