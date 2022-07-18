<?php

declare(strict_types=1);

namespace DI\Test\UnitTest\Definition\Source\Fixtures;

use DI\Attribute\Inject;

class AnnotationFixture5
{
    #[Inject]
    public foobar $property;

    public function __construct(foobar $foo)
    {
    }
}
