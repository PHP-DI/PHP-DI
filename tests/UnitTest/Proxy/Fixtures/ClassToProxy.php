<?php

namespace DI\Test\UnitTest\Proxy\Fixtures;

class ClassToProxy
{
    public function foo()
    {
    }

    public function getInstance()
    {
        return $this;
    }
}
