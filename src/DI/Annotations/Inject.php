<?php

namespace DI\Annotations;

/**
 * "Inject" annotation
 * @Annotation
 * @Target("PROPERTY")
 */
class Inject {

	/**
	 * Bean name
	 * @var string
	 */
	public $name;

	/**
	 * @var boolean
	 */
	public $lazy = false;

}
