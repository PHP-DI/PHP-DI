<?php

namespace DI\Annotations;

/**
 * "Inject" annotation
 * @Annotation
 * @Target("PROPERTY")
 */
class Inject {

	/**
	 * @var string Classname of the instance
	 */
	public $class;

}
