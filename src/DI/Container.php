<?php

namespace DI;

use DI\Annotations\AnnotationException;
use DI\Annotations\Inject;
use DI\Annotations\Value;
use DI\Factory\FactoryInterface;
use DI\Factory\SingletonFactory;
use DI\Injector\DependencyInjector;
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
	 * Factory to use to create instances
	 * @var FactoryInterface
	 */
	private $factory;

	/**
	 * @var DependencyInjector
	 */
	private $dependencyInjector;

	/**
	 * Array of instances/class names to use for abstract classes and interfaces
	 * @var mixed[] implementation[name] The name is the var type or the bean name,
	 * the implementation can be another class name (string) or an instance
	 */
	private $beanMap = array();

	/**
	 * Array of the values to inject with the Value annotation
	 * @var array value[key]
	 */
	private $valueMap = array();

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
		$this->factory = new SingletonFactory();
		$this->dependencyInjector = new DependencyInjector();
	}

	private final function __clone() {}

	/**
	 * Resolve the dependencies of the object
	 *
	 * @param mixed $object Object in which to resolve dependencies
	 * @throws Annotations\AnnotationException
	 */
	public function resolveDependencies($object) {
		if (is_null($object)) {
			return;
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
				$this->dependencyInjector->inject($object, $property, $injectAnnotation,
					$this->beanMap, $this->factory);
			} elseif ($valueAnnotation) {
				$this->resolveValue($property, $valueAnnotation->key, $object);
			}
		}
	}

	/**
	 * @return FactoryInterface the factory used for creating instances
	 */
	public function getFactory() {
		return $this->factory;
	}

	/**
	 * @param FactoryInterface $factory the factory to use for creating instances
	 */
	public function setFactory(FactoryInterface $factory) {
		$this->factory = $factory;
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
		// Factory
		if (isset($data['di.factory']) && class_exists($data['di.factory'])) {
			$factoryClassname = $data['di.factory'];
			if (! class_exists($factoryClassname)) {
				throw new DependencyException("The factory class '$factoryClassname' "
					. "defined in the configuration file '$configurationFile' "
					. "doesn't exist");
			}
			$factory = new $factoryClassname();
			if (! $factory instanceof FactoryInterface) {
				throw new DependencyException("The factory class '$factoryClassname' "
					. "doesn't implement the \\DI\\Factory\\FactoryInterface");
			}
			$this->setFactory($factory);
		}
		// Implementation map
		if (isset($data['di.implementation.map']) && is_array($data['di.implementation.map'])) {
			$mappings = $data['di.implementation.map'];
			foreach ($mappings as $contract => $implementation) {
				$this->addInstancesMapping($contract, $implementation);
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
	 * @param string|mixed $implementation Can be a class name (to instantiate) or an instance
	 */
	public function addInstancesMapping($contractType, $implementation) {
		$this->beanMap[$contractType] = $implementation;
	}

	/**
	 * Resolve the Value annotation on a property
	 * @param \ReflectionProperty $property
	 * @param string              $key
	 * @param                     $object
	 * @throws DependencyException
	 * @throws Annotations\AnnotationException
	 */
	private function resolveValue(\ReflectionProperty $property, $key, $object) {
		if (! isset($this->valueMap[$key])) {
			throw new AnnotationException("@Value was found on " . get_class($object) . "::"
				. $property->getName() . " but the key '$key' can't be resolved");
		}
		$value = $this->valueMap[$key];
		// Inject the value
		$property->setAccessible(true);
		$property->setValue($object, $value);
	}

	/**
	 * Annotation reader
	 * @return \Doctrine\Common\Annotations\AnnotationReader
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

}
