<?php

namespace DI\Injector;

use \DI\Container;
use \DI\BeanNotFoundException;
use \DI\DependencyException;
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
	 */
	public function inject($object, \ReflectionProperty $property, Inject $annotation,
						   Container $container
	) {
		// Allow access to protected and private properties
		$property->setAccessible(true);
		// Consider only not set properties
		if ($property->getValue($object) !== null) {
			return;
		}
		// Get the dependency
		try {
			$dependencyInstance = $container->get($annotation->name, $annotation->lazy);
		} catch (BeanNotFoundException $e) {
			// Better exception message
			throw new BeanNotFoundException("@Inject was found on "
				. get_class($object) . "::" . $property->getName()
				. " but no bean '$annotation->name' was found");
		} catch (\Exception $e) {
			throw new DependencyException("Error while injecting $annotation->name in "
				. get_class($object) . "::" . $property->getName() . ". "
				. $e->getMessage(), 0, $e);
		}
		// Inject the dependency
		$property->setValue($object, $dependencyInstance);
	}

}
