<?php

namespace DI\Test\IntegrationTest\Issues\Issue72;

use DI\Annotation\Inject;

class Class1
{
    public $arg1;

    /**
     * @Inject({"service1"})
     */
    public function __construct(\stdClass $arg1)
    {
        $this->arg1 = $arg1;
    }
}
