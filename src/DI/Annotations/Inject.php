<?php

namespace DI\Annotations;

/**
 * "Inject" annotation
 * @Annotation
 * @Target("PROPERTY")
 */
class Inject {

	/**
	 * @var boolean
	 */
	public $lazy = false;

}
