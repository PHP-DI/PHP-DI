<?php

class NewBenchClass
{

	public function test() {
		$service = new NewDependency();
		$service->foo();
	}

}
