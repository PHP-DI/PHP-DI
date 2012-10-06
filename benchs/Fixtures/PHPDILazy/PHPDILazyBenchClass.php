<?php

use DI\Annotations\Inject;

class PHPDILazyBenchClass
{

	/**
	 * @Inject
	 * @var PHPDILazyDependency
	 */
	private $service;

	public function test() {
		$this->service->foo();
	}

}
