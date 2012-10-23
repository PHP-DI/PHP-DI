<?php

namespace Benchmarks\DI\Fixtures\Singleton;

class SingletonBenchClass
{

	public function test() {
		$service = SingletonDependency::getInstance();
		$service->foo();
	}

}
