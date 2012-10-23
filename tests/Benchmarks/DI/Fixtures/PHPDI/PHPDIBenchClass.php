<?php

namespace Benchmarks\DI\Fixtures\PHPDI;

use DI\Annotations\Inject;

class PHPDIBenchClass
{

	/**
	 * @Inject
	 * @var \Benchmarks\DI\Fixtures\PHPDI\PHPDIDependency
	 */
	private $service;

	public function test() {
		$this->service->foo();
	}

}
