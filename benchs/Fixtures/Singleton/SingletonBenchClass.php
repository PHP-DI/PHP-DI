<?php

class SingletonBenchClass
{

	public function test() {
		$service = SingletonDependency::getInstance();
		$service->foo();
	}

}
