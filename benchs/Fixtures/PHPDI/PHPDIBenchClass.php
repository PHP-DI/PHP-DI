<?php

use DI\Annotations\Inject;

class PHPDIBenchClass
{

	/**
	 * @Inject
	 * @var PHPDIDependency
	 */
	private $service;

	public function test() {
		$this->service->foo();
	}

}
