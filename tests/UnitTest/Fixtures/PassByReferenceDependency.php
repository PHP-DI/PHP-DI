<?php

namespace DI\Test\UnitTest\Fixtures;

use stdClass;

class PassByReferenceDependency
{
	public function __construct(stdClass &$object)
	{
		$object->foo = 'bar';
	}
}
