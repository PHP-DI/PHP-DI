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
        $this->factory = new Factory();
    }

    /**
     * Resolve the dependencies of the object
     *
     * @param \Object $object Object in which to resolve dependencies
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
            $inject = false;
            $propertyType = null;
            $propertyAnnotations = $this->getAnnotationReader()->getPropertyAnnotations($property);
            foreach ($propertyAnnotations as $annotation) {
                if ($annotation instanceof Inject) {
                    $inject = true;
                }
            }
            if ($inject == false) {
                continue;
            }
            $propertyType = $this->getPropertyType($property);
            // Injection
            if ($inject && ($propertyType != null)) {
                $dependencyInstance = $this->factory->getInstance($propertyType);
                $property->setAccessible(true);
                $property->setValue($object, $dependencyInstance);
            } elseif ($inject && ($propertyType == null)) {
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
     * @param $factory the factory to use for creating instances
     */
    public function setFactory(FactoryInterface $factory) {
        $this->factory = $factory;
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
            //$annotationReader->setDefaultAnnotationNamespace('DI\Annotations\\');
	    }
        return $annotationReader;
	}

    /**
     * Parse the docblock of the property to get the @var annotation
     * @param \ReflectionProperty $property
     * @return string Type of the property (content of @var annotation)
     */
    private function getPropertyType(\ReflectionProperty $property) {
        if (preg_match('/@var\s+([^\s]+)/', $property->getDocComment(), $matches)) {
            list(, $type) = $matches;
            return $type;
        }
        return null;
    }

}
