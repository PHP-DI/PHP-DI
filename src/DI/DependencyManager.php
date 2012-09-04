<?php

namespace DI;

use DI\Annotations\Inject;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\AnnotationReader;

/**
 * Dependency manager
 */
class DependencyManager {

    /**
     * Factory to use to create instances
     * @var FactoryInterface
     */
	protected $factory;

	/**
	 * Array of implementations for abstract classes and interfaces
	 * @var implementation[contract]
	 */
	protected $implementationMapping = array();

    /**
     * Returns an instance of the class
     * @return \DI\DependencyManager
     */
    public static function getInstance()
    {
        static $instance;
        if ($instance == null) {
            $instance = new self();
        }
        return $instance;
    }

    /**
     * Protected constructor because of singleton
     */
    protected function __construct() {
        $this->factory = new DefaultFactory();
    }

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
				// Try to find a mapping for the implementation to use
				if (array_key_exists($classname, $this->implementationMapping)) {
					$classname = $this->implementationMapping[$classname];
				}
				try {
                	$dependencyInstance = $this->factory->getInstance($classname);
				} catch (FactoryException $e) {
					throw new DependencyException("Error while injecting $classname in "
						. $reflectionClass->getName() . "::" . $property->getName() . ". " . $e->getMessage());
				}
                $property->setAccessible(true);
                $property->setValue($object, $dependencyInstance);
            } elseif ($injectAnnotation && (! $classname)) {
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
	 * @param string $configurationFile the php-di configuration file
	 * @throws \Exception
	 */
	public function setConfiguration($configurationFile) {
		if (! (file_exists($configurationFile) && is_readable($configurationFile))) {
			throw new \Exception("Configuration file $configurationFile doesn't exist or is not readable");
		}
		// Read ini file
		$data = parse_ini_file($configurationFile);
		$mappings = $data['di.implementation.map'];
		if ($mappings && is_array($mappings)) {
			foreach ($mappings as $contract => $implementation) {
				$this->addImplementationMapping($contract, $implementation);
			}
		}
	}

	/**
	 * Map the implementation to use for an abstract class or interface
	 * @param $contractType the abstract class or interface name
	 * @param $implementationType the class to use as the implementation
	 */
	public function addImplementationMapping($contractType, $implementationType) {
		$this->implementationMapping[$contractType] = $implementationType;
	}

	/**
	 * Annotation reader
	 * @return \Doctrine\Common\Annotations\AnnotationReader
	 */
	private function getAnnotationReader()
	{
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
