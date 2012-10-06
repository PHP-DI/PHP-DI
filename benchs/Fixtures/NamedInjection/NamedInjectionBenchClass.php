<?php

use DI\Annotations\Inject;

class NamedInjectionBenchClass
{

	/**
	 * @Inject(name="myBean")
	 */
	private $service;

	public function test() {
		$this->service->foo();
	}

}
