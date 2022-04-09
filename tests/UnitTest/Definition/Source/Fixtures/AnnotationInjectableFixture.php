<?php

declare(strict_types=1);

namespace DI\Test\UnitTest\Definition\Source\Fixtures;

use DI\Attribute\Injectable;

#[Injectable(lazy: true)]
class AnnotationInjectableFixture
{
}
