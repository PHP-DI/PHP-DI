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
use ReflectionMethod;
use ReflectionClass;
use ReflectionProperty;
use DI\Annotations\AnnotationException;
use DI\MetadataReader\DefaultMetadataReader;
use DI\Annotations\Inject;
use DI\MetadataReader\MetadataReader;
use DI\Proxy\Proxy;

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
	 * @var MetadataReader
	 */
	private $metadataReader;


	/**
	 * Returns an instance of the class (Singleton design pattern)
	 * @return \DI\Container
	 */
	public static function getInstance() {
		if (self::$singletonInstance == null) {
			self::$singletonInstance = new self();
		}
		return self::$singletonInstance;
	}

	/**
	 * Reset the singleton instance, for the tests only
	 */
	public static function reset() {
		self::$singletonInstance = null;
	}

	/**
	 * Applies the configuration given
	 * @param array $configuration See the documentation
	 */
	public static function addConfiguration(array $configuration) {
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
				$container->set($from, function (Container $c) use ($to) {
					return $c->get($to);
				});
			}
		}
	}

	/**
	 * Protected constructor because of singleton
	 */
	protected function __construct() {
	}

	/**
	 * Returns an instance by its name
	 *
	 * @param string $name Can be a bean name or a class name
	 * @param bool   $useProxy If true, returns a proxy class of the instance
	 *                            if it is not already loaded
	 * @throws NotFoundException
	 * @throws DependencyException
	 * @return mixed Instance
	 */
	public function get($name, $useProxy = false) {
		if (! is_string($name)) {
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
	public function set($name, $entry) {
		$this->entries[$name] = $entry;
	}

	/**
	 * Inject the dependencies of the object (marked with the Inject annotation)
	 *
	 * @param mixed $object Object in which to resolve dependencies
	 * @throws Annotations\AnnotationException
	 * @throws DependencyException
	 */
	public function injectAll($object) {
		if (is_null($object)) {
			throw new DependencyException("null given, object instance expected");
		}
		// Get the class metadata
		$classMetadata = $this->getMetadataReader()->getClassMetadata(get_class($object));
		// Process annotations on methods
		foreach ($classMetadata->getMethodAnnotations() as $methodName => $annotation) {
			if ($annotation instanceof Inject) {
				$this->injectMethod($object, $methodName, $annotation);
			}
		}
		// Process annotations on properties
		foreach ($classMetadata->getPropertyAnnotations() as $propertyName => $annotation) {
			if ($annotation instanceof Inject) {
				$this->injectProperty($object, $propertyName, $annotation);
			}
		}
	}

	/**
	 * @return MetadataReader The metadata reader
	 */
	public function getMetadataReader() {
		if ($this->metadataReader == null) {
			$this->metadataReader = new DefaultMetadataReader();
		}
		return $this->metadataReader;
	}

	/**
	 * @param MetadataReader $metadataReader The metadata reader
	 */
	public function setMetadataReader(MetadataReader $metadataReader) {
		$this->metadataReader = $metadataReader;
	}

	/**
	 * Whether an offset exists
	 *
	 * @link http://php.net/manual/en/arrayaccess.offsetexists.php
	 * @param mixed $offset An offset to check for
	 * @return boolean true on success or false on failure
	 */
	public function offsetExists($offset) {
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
	public function offsetGet($offset) {
		return $this->get($offset);
	}

	/**
	 * Offset to set
	 *
	 * @link http://php.net/manual/en/arrayaccess.offsetset.php
	 * @param mixed $offset The offset to assign the value to
	 * @param mixed $value The value to set
	 */
	public function offsetSet($offset, $value) {
		$this->set($offset, $value);
	}

	/**
	 * Offset to unset
	 *
	 * @link http://php.net/manual/en/arrayaccess.offsetunset.php
	 * @param mixed $offset The offset to unset
	 */
	public function offsetUnset($offset) {
		unset($this->entries[$offset]);
	}

	/**
	 * Returns a proxy class
	 *
	 * @param string $classname
	 * @return \DI\Proxy\Proxy Proxy instance
	 */
	private function getProxy($classname) {
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
	 * @return string the instance
	 */
	private function getNewInstance($classname) {
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
		return $instance;
	}

	/**
	 * Creates a new instance of a class without calling its constructor
	 *
	 * @param ReflectionClass $classReflection
	 * @return mixed|void
	 */
	private function newInstanceWithoutConstructor(ReflectionClass $classReflection) {
		if (version_compare(PHP_VERSION, '5.4.0') >= 0) {
			// Create a new class instance without calling the constructor (PHP 5.4 magic)
			return $classReflection->newInstanceWithoutConstructor();
		} else {
			$classname = $classReflection->getName();
			return unserialize(
				sprintf(
					'O:%d:"%s":0:{}',
					strlen($classname), $classname
				));
		}
	}

	/**
	 * Inject dependencies through the constructor
	 * @param mixed            $object
	 * @param ReflectionMethod $constructorReflection
	 */
	private function injectConstructor($object, ReflectionMethod $constructorReflection) {
		$args = array();
		foreach ($constructorReflection->getParameters() as $parameter) {
			$parameterClass = $parameter->getClass();
			if ($parameterClass === null) {
				throw new AnnotationException("The parameter {$parameter->name} of the constructor of $parameterClass"
					. " has no type: impossible to deduce its type");
			}
			$args[] = $this->get($parameterClass->name);
		}
		$constructorReflection->invokeArgs($object, $args);
	}

	/**
	 * Resolve the Inject annotation on a method
	 * @param mixed  $object Object to inject dependencies to
	 * @param string $methodName Name of the method annotated
	 * @param Inject $annotation Inject annotation
	 * @throws DependencyException
	 * @throws NotFoundException
	 */
	private function injectMethod($object, $methodName, Inject $annotation) {
		$classname = get_class($object);
		// Get the dependency
		try {
			$value = $this->get($annotation->name, $annotation->lazy);
		} catch (NotFoundException $e) {
			// Better exception message
			throw new NotFoundException("@Inject was found on $classname::$methodName(...)"
				. " but no bean or value '$annotation->name' was found");
		} catch (\Exception $e) {
			throw new DependencyException("Error while injecting {$annotation->name} to $classname::$methodName(...). "
				. $e->getMessage(), 0, $e);
		}
		// Inject the dependency by calling the method
		call_user_func_array(array($object, $methodName), array($value));
	}

	/**
	 * Resolve the Inject annotation on a property
	 * @param mixed  $object Object to inject dependencies to
	 * @param string $propertyName Name of the property annotated
	 * @param Inject $annotation Inject annotation
	 * @throws DependencyException
	 * @throws NotFoundException
	 */
	private function injectProperty($object, $propertyName, Inject $annotation) {
		$property = new ReflectionProperty(get_class($object), $propertyName);
		// Allow access to protected and private properties
		$property->setAccessible(true);
		// Consider only not set properties
		if ($property->getValue($object) !== null) {
			return;
		}
		// Get the dependency
		try {
			$value = $this->get($annotation->name, $annotation->lazy);
		} catch (NotFoundException $e) {
			// Better exception message
			throw new NotFoundException("@Inject was found on " . get_class($object) . "::" . $property->getName()
				. " but no bean or value '$annotation->name' was found");
		} catch (\Exception $e) {
			throw new DependencyException("Error while injecting $annotation->name in "
				. get_class($object) . "::" . $property->getName() . ". "
				. $e->getMessage(), 0, $e);
		}
		// Inject the dependency
		$property->setValue($object, $value);
	}

	private final function __clone() {
	}

}
