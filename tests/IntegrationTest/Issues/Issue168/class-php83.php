<?php

declare(strict_types=1);

namespace DI\Test\IntegrationTest\Issues\Issue168;

class TestClass83
{
    /**
     * The parameter is optional. TestInterface is not instantiable, so `null` should
     * be injected instead of getting an exception.
     */
    public function __construct(TestInterface83 $param = null)
    {
    }
}

interface TestInterface83
{
}
