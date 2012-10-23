<?php

namespace Benchmarks\DI\Fixtures\NewInstance;

class NewBenchClass
{

	public function test() {
		$service = new NewDependency();
		$service->foo();
	}

}
