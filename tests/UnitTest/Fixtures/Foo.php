<?php

namespace DI\Test\UnitTest\Fixtures;

class Foo
{
    public $bar;

    public function __construct($bar)
    {
        $this->bar = $bar;
    }
}