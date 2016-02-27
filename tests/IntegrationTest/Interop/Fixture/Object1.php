<?php

namespace DI\Test\IntegrationTest\Interop\Fixture;

class Object1
{
    public $param1;

    public function __construct($param1)
    {
        $this->param1 = $param1;
    }
}
