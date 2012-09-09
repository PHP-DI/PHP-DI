<?php

namespace DI;

use DI\Annotations\Inject;
use DI\Factory\FactoryInterface;
use DI\Factory\SingletonFactory;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\AnnotationReader;

/**
 * Dependency manager
 *
 * This class uses the resettable Singleton pattern (resettable for the tests).
 */
class DependencyManager
{

	private static $singletonInstance = null;

	/**
	 * Factory to use to create instances
	 * @var FactoryInterface
	 */
	protected $factory;

	/**
	 * Array of instances/class names to use for abstract classes and interfaces
	 * @var mixed[] implementation[name] The name is the var type,
	 * the implementation can be a class name (string) or an instance
	 */
	protected $instancesMapping = array();

	/**
	 * Returns an instance of the class (Singleton design pattern)
	 * @return \DI\DependencyManager
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
	}

	private final function __clone() {}

	/**
	 * Resolve the dependencies of the object
	 *
	 * @param mixed $object Object in which to resolve dependencies
	 * @throws \Exception
	 * @throws DependencyException
	 */
	public function resolveDependencies($object) {
		if (is_null($object)) {
			return;
		}
		// Properties
		$reflectionClass = new \ReflectionObject($object);
		$properties = $reflectionClass->getProperties();
		foreach ($properties as $property) {
			// Look for @Inject and @var
			$injectAnnotation = null;
			$classname = null;
			$propertyAnnotations = $this->getAnnotationReader()->getPropertyAnnotations($property);
			foreach ($propertyAnnotations as $annotation) {
				if ($annotation instanceof Inject) {
					$injectAnnotation = $annotation;
				}
			}
			// If no @Inject annotation, continue
			if ($injectAnnotation == null) {
				continue;
			}
			// Find the type of the class to inject
			$classname = $this->getPropertyType($property);
			// Injection
			if ($injectAnnotation && $classname) {

				$dependencyInstance = null;

				// Try to find a mapping for the implementation to use
				if (array_key_exists($classname, $this->instancesMapping)) {
					$tmp = $this->instancesMapping[$classname];
					if (is_string($tmp)) {
						// Override the class name, will use the factory to get an instance
						$classname = $tmp;
					} else {
						// This is an instance
						$dependencyInstance = $tmp;
					}
				}

				// Use the factory to get an instance
				if ($dependencyInstance === null) {
					try {
						$dependencyInstance = $this->factory->getInstance($classname);
					} catch (\Exception $e) {
						throw new DependencyException("Error while injecting $classname in "
							. $reflectionClass->getName() . "::" . $property->getName() . ". " . $e->getMessage(), 0, $e);
					}
				}

				// Inject the dependency
				$property->setAccessible(true);
				$property->setValue($object, $dependencyInstance);
			} elseif ($injectAnnotation && (!$classname)) {
				throw new \Exception("@Inject was found on " . get_class($object) . "::"
					. $property->getName() . " but no @var annotation.");
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
					. "doesn't implement the \DI\Factory\FactoryInterface");
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
	}

	/**
	 * Map the implementation to use for an abstract class or interface
	 * @param string $contractType the abstract class or interface name
	 * @param string|mixed $implementation Can be a class name (to instantiate) or an instance
	 */
	public function addInstancesMapping($contractType, $implementation) {
		$this->instancesMapping[$contractType] = $implementation;
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

	/**
	 * Parse the docblock of the property to get the var annotation
	 * @param \ReflectionProperty $property
	 * @return string Type of the property (content of var annotation)
	 */
	private function getPropertyType(\ReflectionProperty $property) {
		if (preg_match('/@var\s+([^\s]+)/', $property->getDocComment(), $matches)) {
			list(, $type) = $matches;
			return $type;
		}
		return null;
	}

}
