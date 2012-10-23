<?php

namespace Benchmarks\DI\Fixtures\PHPDILazy;

use DI\Annotations\Inject;

class PHPDILazyBenchClass
{

	/**
	 * @Inject
	 * @var \Benchmarks\DI\Fixtures\PHPDILazy\PHPDILazyDependency
	 */
	private $service;

	public function test() {
		$this->service->foo();
	}

}
