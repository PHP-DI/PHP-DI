<?php

declare(strict_types=1);

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
