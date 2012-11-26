<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.com/PHP-DI/
 * @copyright 2012 Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\MetadataReader;

use DI\Annotations\AnnotationException;
use DI\Annotations\Value;
use DI\Annotations\Inject;
use InvalidArgumentException;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\Reader;

/**
 * Reads PHP class metadata such as @ Inject and @ var annotations
 *
 * Uses Reflection, Doctrine's Annotations and regex docblock parsing
 */
class DefaultMetadataReader implements MetadataReader
{

	/**
	 * @var \Doctrine\Common\Annotations\Reader
	 */
	private $annotationReader;

	/**
	 * Returns DI annotations found in the class
	 * @param string $classname
	 * @throws \DI\Annotations\AnnotationException
	 * @throws \InvalidArgumentException The class doesn't exist
	 * @return array Array of annotations indexed by the property name
	 */
	public function getClassMetadata($classname) {
		if (!class_exists($classname)) {
			throw new InvalidArgumentException("The class $classname doesn't exist");
		}
		$annotations = array();
		// Browse the object's properties
		$reflectionClass = new \ReflectionClass($classname);
		foreach ($reflectionClass->getProperties() as $property) {

			// Look for DI annotations
			$propertyAnnotations = $this->getAnnotationReader()->getPropertyAnnotations($property);
			foreach ($propertyAnnotations as $annotation) {
				// @Inject
				if ($annotation instanceof Inject) {
					// Enrich @Inject annotation with @var content
					if ($annotation->name == null) {
						$propertyType = $this->getPropertyType($property);
						if ($propertyType == null) {
							throw new AnnotationException("@Inject was found on $classname::"
								. $property->getName() . " but no (or empty) @var annotation");
						}
						$annotation->name = $propertyType;
					}
					$annotations[$property->getName()] = $annotation;
					break;
				}
				// @Value
				if ($annotation instanceof Value) {
					$annotations[$property->getName()] = $annotation;
					break;
				}
			}

		}
		return $annotations;
	}

	/**
	 * @return Reader The annotation reader
	 */
	public function getAnnotationReader() {
		if ($this->annotationReader == null) {
			AnnotationRegistry::registerAutoloadNamespace('DI\Annotations', __DIR__ . '/../../');
			$this->annotationReader = new AnnotationReader();
		}
		return $this->annotationReader;
	}

	/**
	 * @param Reader $annotationReader The annotation reader
	 */
	public function setAnnotationReader(Reader $annotationReader) {
		$this->annotationReader = $annotationReader;
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
