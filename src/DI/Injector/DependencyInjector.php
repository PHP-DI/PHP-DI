<?php

namespace DI\Injector;

use \DI\BeanNotFoundException;
use \DI\DependencyException;
use \DI\Annotations\AnnotationException;
use \DI\Annotations\Inject;
use \DI\Factory\FactoryInterface;
use \DI\Proxy\Proxy;

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
	 * @param array               $typeMap Map of the class types
	 * @param array               $beanMap Map of the beans
	 * @param FactoryInterface    $factory Factory for instanciating beans
	 * @throws DependencyException
	 * @throws BeanNotFoundException
	 * @throws AnnotationException
	 */
	public function inject($object, \ReflectionProperty $property, Inject $annotation,
						   array $typeMap, array $beanMap, FactoryInterface $factory
	) {
		$dependencyInstance = null;
		if ($annotation->name != null) {
			// Named bean
			$dependencyInstance = $this->resolveNamedBean($object, $property, $annotation, $beanMap);
		} else {
			// Not named bean
			$dependencyInstance = $this->resolveNotNamedBean($object, $property, $annotation,
				$typeMap, $factory);
		}

		// Inject the dependency
		$property->setAccessible(true);
		$property->setValue($object, $dependencyInstance);
	}

	/**
	 * Resolve the Inject(name="...") annotation
	 * @param mixed               $object Object to inject dependencies to
	 * @param \ReflectionProperty $property Property annotated
	 * @param Inject              $annotation Inject annotation
	 * @param array               $beanMap Map of the beans
	 * @return object Dependency to inject
	 * @throws BeanNotFoundException
	 */
	private function resolveNamedBean($object, \ReflectionProperty $property, Inject $annotation,
									 array $beanMap
	) {
		$beanName = $annotation->name;
		if (array_key_exists($beanName, $beanMap)) {
			return $beanMap[$beanName];
		} else {
			throw new BeanNotFoundException("@Inject(name='$beanName') was found on "
				. get_class($object) . "::" . $property->getName()
				. " but no bean named '$beanName' was found");
		}
	}

	/**
	 * Resolve the Inject annotation without a specific name
	 * @param mixed               $object Object to inject dependencies to
	 * @param \ReflectionProperty $property Property annotated
	 * @param Inject              $annotation Inject annotation
	 * @param array               $typeMap Map of the class types
	 * @param FactoryInterface    $factory Factory for instanciating beans
	 * @return object Dependency to inject
	 * @throws DependencyException
	 * @throws AnnotationException
	 */
	private function resolveNotNamedBean($object, \ReflectionProperty $property, Inject $annotation,
										array $typeMap, FactoryInterface $factory
	) {
		$dependencyInstance = null;
		// Not named bean => we use the @var annotation
		$dependencyType = $this->getPropertyType($property);
		if ($dependencyType == null) {
			throw new AnnotationException("@Inject was found on " . get_class($object) . "::"
				. $property->getName() . " but no @var annotation");
		}

		// Try to find a mapping for the implementation to use
		if (array_key_exists($dependencyType, $typeMap)) {
			$tmp = $typeMap[$dependencyType];
			if (is_string($tmp)) {
				// Override the dependency type, will use the factory to get an instance
				$dependencyType = $tmp;
			} else {
				// This is an instance
				$dependencyInstance = $tmp;
			}
		}

		// Use the factory to get an instance
		if ($dependencyInstance === null) {
			try {
				if ($annotation->lazy) {
					// Lazy loading for the dependency: inject a proxy class
					$dependencyInstance = new Proxy(function() use ($factory, $dependencyType) {
						return $factory->getInstance($dependencyType);
					});
				} else {
					$dependencyInstance = $factory->getInstance($dependencyType);
				}
			} catch (\Exception $e) {
				throw new DependencyException("Error while injecting $dependencyType in "
					. get_class($object) . "::" . $property->getName() . ". "
					. $e->getMessage(), 0, $e);
			}
		}

		return $dependencyInstance;
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
