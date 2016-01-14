<?php

namespace DI\Test\IntegrationTest\Annotations;

use DI\Annotation\Inject;

class Child extends B
{
    /**
     * @Inject
     * @var A
     */
    private $private;

    public function getChildPrivate()
    {
        return $this->private;
    }
}
