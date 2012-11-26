<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.com/PHP-DI/
 * @copyright 2012 Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Injector;

use \DI\Annotations\AnnotationException;
use \DI\Annotations\Value;

/**
 * Class injecting values
 */
class ValueInjector
{

	/**
	 * Resolve the Value annotation on a property
	 * @param mixed               $object Object to inject dependencies to
	 * @param \ReflectionProperty $property Property annotated
	 * @param Value               $annotation Value annotation
	 * @param array               $valueMap Map of values
	 * @throws AnnotationException
	 */
	public function inject($object, \ReflectionProperty $property, Value $annotation,
							array $valueMap
	) {
		// Allow access to protected and private properties
		$property->setAccessible(true);
		// Get the value
		$key = $annotation->key;
		if (! isset($valueMap[$key])) {
			throw new AnnotationException("@Value was found on " . get_class($object) . "::"
				. $property->getName() . " but the key '$key' can't be resolved");
		}
		$value = $valueMap[$key];
		// Inject the value
		$property->setValue($object, $value);
	}

}
