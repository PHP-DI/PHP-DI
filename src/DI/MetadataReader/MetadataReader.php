<?php

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
