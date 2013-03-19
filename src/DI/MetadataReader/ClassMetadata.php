<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.com/PHP-DI/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\MetadataReader;

use DI\Annotations\Inject;

/**
 * Class metadata
 */
class ClassMetadata
{

	/**
	 * Property annotations indexed by the property name
	 * @var Inject[]
	 */
	private $propertyAnnotations = array();

	/**
	 * Method annotations indexed by the method name
	 * @var Inject[]
	 */
	private $methodAnnotations = array();


	/**
	 * @return Inject[] Property annotations indexed by the property name
	 */
	public function getAllPropertyAnnotations() {
		return $this->propertyAnnotations;
	}

	/**
	 * @param string $propertyName
	 * @param Inject $propertyAnnotation
	 */
	public function addPropertyAnnotation($propertyName, Inject $propertyAnnotation) {
		$this->propertyAnnotations[$propertyName] = $propertyAnnotation;
	}

	/**
	 * @param Inject[] $propertyAnnotations
	 */
	public function setPropertyAnnotations(array $propertyAnnotations) {
		$this->propertyAnnotations = $propertyAnnotations;
	}

	/**
	 * @return Inject[] Method annotations indexed by the method name
	 */
	public function getAllMethodAnnotations() {
		return $this->methodAnnotations;
	}

	/**
	 * @param string $methodName
	 * @param Inject $methodAnnotation
	 */
	public function addMethodAnnotation($methodName, Inject $methodAnnotation) {
		$this->methodAnnotations[$methodName] = $methodAnnotation;
	}

	/**
	 * @param Inject[] $methodAnnotations
	 */
	public function setMethodAnnotations(array $methodAnnotations) {
		$this->methodAnnotations = $methodAnnotations;
	}

	/**
	 * Serialization
	 * @return array
	 */
	public function __sleep() {
		return array(
			'propertyAnnotations',
			'methodAnnotations',
		);
	}

}
