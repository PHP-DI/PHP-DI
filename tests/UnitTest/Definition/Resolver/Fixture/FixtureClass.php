<?php

namespace DI\Test\UnitTest\Definition\Resolver\Fixture;

class FixtureClass
{
    public $prop;
    public $constructorParam1;
    public $methodParam1;
    public $methodParam2;

    public function __construct($param1)
    {
        $this->constructorParam1 = $param1;
    }

    public function method($param1)
    {
        $this->methodParam1 = $param1;
    }

    public function methodDefaultValue($param = 'defaultValue')
    {
        $this->methodParam2 = $param;
    }
}
