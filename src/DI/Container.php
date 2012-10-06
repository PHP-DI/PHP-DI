<?php

namespace DI;

use DI\Annotations\AnnotationException;
use DI\Annotations\Inject;
use DI\Annotations\Value;
use DI\Injector\DependencyInjector;
use DI\Injector\ValueInjector;
use DI\Proxy\Proxy;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\AnnotationReader;

/**
 * Container
 *
 * This class uses the resettable Singleton pattern (resettable for the tests).
 */
class Container
{

	private static $singletonInstance = null;

	/**
	 * Map of instances
	 * @var array object[name]
	 */
	private $beanMap = array();

	/**
	 * Array of the values to inject with the Value annotation
	 * @var array value[key]
	 */
	private $valueMap = array();

	/**
	 * Map of instances/class names to use for abstract classes and interfaces
	 * @var array array(interface => implementation)
	 */
	private $classAliases = array();

	/**
	 * @var DependencyInjector
	 */
	private $dependencyInjector;

	/**
	 * @var ValueInjector
	 */
	private $valueInjector;

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
	 * Protected constructor because of singleton
	 */
	protected function __construct() {
		$this->dependencyInjector = new DependencyInjector();
		$this->valueInjector = new ValueInjector();
	}

	/**
	 * Returns an instance by its name
	 *
	 * @param string $name Can be a bean name or a class name
	 * @param bool   $useProxy If true, returns a proxy class of the instance
	 * 						   if it is not already loaded
	 * @return mixed Instance
	 * @throws BeanNotFoundException
	 */
	public function get($name, $useProxy = false) {
		if (array_key_exists($name, $this->beanMap)) {
			return $this->beanMap[$name];
		}
		$className = $name;
		// Try to find a mapping for the implementation to use
		if (array_key_exists($name, $this->classAliases)) {
			$className = $this->classAliases[$className];
		}
		// Try to find the bean
		if (array_key_exists($className, $this->beanMap)) {
			return $this->beanMap[$className];
		}
		// Instance not found, use the factory to create it
		if (class_exists($className)) {
			if (!$useProxy) {
				$this->beanMap[$className] = $this->getNewInstance($className);
				return $this->beanMap[$className];
			} else {
				// Return a proxy class
				return $this->getProxy($className);
			}
		}
		throw new BeanNotFoundException("No bean or class named '$name' was found");
	}

	/**
	 * Define a bean in the container
	 *
	 * @param string $name Bean name or class name to be used with Inject annotation
	 * @param object $instance Instance
	 */
	public function set($name, $instance) {
		$this->beanMap[$name] = $instance;
	}

	/**
	 * Resolve the dependencies of the object
	 *
	 * @param mixed $object Object in which to resolve dependencies
	 * @throws Annotations\AnnotationException
	 * @throws DependencyException
	 */
	public function resolveDependencies($object) {
		if (is_null($object)) {
			throw new DependencyException("null given, object instance expected");
		}
		// Fetch the object's properties
		$reflectionClass = new \ReflectionObject($object);
		$properties = $reflectionClass->getProperties();
		// For each property
		foreach ($properties as $property) {
			// Look for DI annotations
			$injectAnnotation = null;
			$valueAnnotation = null;
			$propertyAnnotations = $this->getAnnotationReader()->getPropertyAnnotations($property);
			foreach ($propertyAnnotations as $annotation) {
				if ($annotation instanceof Inject) {
					$injectAnnotation = $annotation;
				}
				if ($annotation instanceof Value) {
					$valueAnnotation = $annotation;
				}
			}
			// If both @Inject and @Value annotation, exception
			if ($injectAnnotation && $valueAnnotation) {
				throw new AnnotationException(get_class($object) . "::" . $property->getName()
					. " can't have both @Inject and @Value annotations");
			} elseif ($injectAnnotation) {
				$this->dependencyInjector->inject($object, $property, $injectAnnotation, $this);
			} elseif ($valueAnnotation) {
				$this->valueInjector->inject($object, $property, $valueAnnotation, $this->valueMap);
			}
		}
	}

	/**
	 * Read and applies the configuration found in the file
	 *
	 * Doesn't reset the configuration to default before importing the file.
	 * @param string $configurationFile the php-di configuration file
	 * @throws \Exception
	 * @throws DependencyException
	 */
	public function addConfigurationFile($configurationFile) {
		if (!(file_exists($configurationFile) && is_readable($configurationFile))) {
			throw new \Exception("Configuration file $configurationFile doesn't exist or is not readable");
		}
		// Read ini file
		$data = parse_ini_file($configurationFile);
		// Implementation map
		if (isset($data['di.types.map']) && is_array($data['di.types.map'])) {
			$mappings = $data['di.types.map'];
			foreach ($mappings as $contract => $implementation) {
				$this->addClassAlias($contract, $implementation);
			}
		}
		// Values map
		if (isset($data['di.values']) && is_array($data['di.values'])) {
			$this->valueMap = array_merge($this->valueMap, $data['di.values']);
		}
	}

	/**
	 * Map the implementation to use for an abstract class or interface
	 * @param string $contractType the abstract class or interface name
	 * @param string $implementationType Class name of the implementation
	 */
	public function addClassAlias($contractType, $implementationType) {
		$this->classAliases[$contractType] = $implementationType;
	}

	/**
	 * Returns a proxy class
	 * @param string $classname
	 * @return Proxy proxy instance
	 */
	private function getProxy($classname) {
		$container = $this;
		return new Proxy(function() use ($container, $classname) {
			// Create the instance and add it to the container
			$instance = new $classname();
			$container->resolveDependencies($instance);
			$container->set($classname, $instance);
			return $instance;
		});
	}

	/**
	 * Create a new instance of the class
	 * @param string $classname Class to instantiate
	 * @return string the instance
	 */
	private function getNewInstance($classname) {
		$instance = new $classname();
		Container::getInstance()->resolveDependencies($instance);
		return $instance;
	}

	/**
	 * Annotation reader
	 * @return AnnotationReader
	 */
	private function getAnnotationReader() {
		static $annotationReader;
		if ($annotationReader == null) {
			AnnotationRegistry::registerAutoloadNamespace('DI\Annotations',
				dirname(__FILE__) . '/../');
			$annotationReader = new AnnotationReader();
		}
		return $annotationReader;
	}

	private final function __clone() {}

}
