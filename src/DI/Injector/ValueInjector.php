<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.com/PHP-DI/
 * @copyright 2012 Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Injector;

use DI\Annotations\AnnotationException;
use DI\Annotations\Value;
use DI\Container;

/**
 * Class injecting values
 * @deprecated
 */
class ValueInjector
{

	/**
	 * Resolve the Value annotation on a property
	 * @param mixed               $object Object to inject dependencies to
	 * @param \ReflectionProperty $property Property annotated
	 * @param Value               $annotation Value annotation
	 * @param Container           $container Map of values
	 * @throws AnnotationException
	 */
	public function inject($object, \ReflectionProperty $property, Value $annotation, Container $container) {
		// Get the value
		$key = $annotation->key;
		if (! isset($container[$key])) {
			throw new AnnotationException("@Value was found on " . get_class($object) . "::"
				. $property->getName() . " but the key '$key' can't be resolved");
		}
		$value = $container[$key];
		// Inject the value
		$property->setAccessible(true);
		$property->setValue($object, $value);
	}

}
