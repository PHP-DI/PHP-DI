<?php

declare(strict_types=1);

namespace DI\Test\UnitTest\Definition\Source\Fixtures;

use DI\Attribute\Inject;

class AnnotationFixture4
{
    #[Inject]
    public $property;
}
