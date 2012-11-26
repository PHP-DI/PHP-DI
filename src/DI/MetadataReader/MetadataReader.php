<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.com/PHP-DI/
 * @copyright 2012 Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\MetadataReader;

/**
 * Reads PHP class metadata such as @ Inject and @ var annotations
 */
interface MetadataReader
{

	/**
	 * Returns DI annotations found in the class
	 * @param string $classname
	 * @return array Array of annotations indexed by the property name
	 */
	public function getClassMetadata($classname);

}
