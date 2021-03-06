<?php

declare(strict_types=1);

namespace DI\Test\IntegrationTest\Attributes;

use DI\Attribute\Inject;

class Child extends B
{
    #[Inject]
    private A $private;

    public function getChildPrivate()
    {
        return $this->private;
    }
}
