<?php

namespace DI\Injector;

use \DI\Container;
use \DI\BeanNotFoundException;
use \DI\DependencyException;
use \DI\Annotations\AnnotationException;
use \DI\Annotations\Inject;

/**
 * Class injecting dependencies
 */
class DependencyInjector
{

	/**
	 * Resolve the Inject annotation on a property
	 * @param mixed               $object Object to inject dependencies to
	 * @param \ReflectionProperty $property Property annotated
	 * @param Inject              $annotation Inject annotation
	 * @param Container           $container Map of the class types
	 * @throws DependencyException
	 * @throws BeanNotFoundException
	 * @throws AnnotationException
	 */
	public function inject($object, \ReflectionProperty $property, Inject $annotation,
						   Container $container
	) {
		$dependencyInstance = null;

		if ($annotation->name != null) {
			// Named bean
			$beanName = $annotation->name;
			try {
				$dependencyInstance = $container->get($beanName);
			} catch(BeanNotFoundException $e) {
				// Better exception message
				throw new BeanNotFoundException("@Inject(name='$beanName') was found on "
					. get_class($object) . "::" . $property->getName()
					. " but no bean named '$beanName' was found");
			}
		} else {
			// Not named bean, use the property type
			$beanName = $this->getPropertyType($property);
			if ($beanName == null) {
				throw new AnnotationException("@Inject was found on " . get_class($object) . "::"
					. $property->getName() . " but no @var annotation");
			}
			// Need to create the instance
			try {
				$dependencyInstance = $container->get($beanName, $annotation->lazy);
			} catch (\Exception $e) {
				throw new DependencyException("Error while injecting $beanName in "
					. get_class($object) . "::" . $property->getName() . ". "
					. $e->getMessage(), 0, $e);
			}
		}

		// Inject the dependency
		$property->setAccessible(true);
		$property->setValue($object, $dependencyInstance);
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
