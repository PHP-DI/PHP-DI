<?php

namespace DI\Test\UnitTest\Definition\Compiler\ObjectDefinition\Fixtures;

class Class2
{
    public function setThing()
    {
    }

    public function setWithParams($param1, $param2)
    {
    }

    public function setWithDefaultValues($param1 = 'foo', $param2 = 'bar')
    {
    }
}
