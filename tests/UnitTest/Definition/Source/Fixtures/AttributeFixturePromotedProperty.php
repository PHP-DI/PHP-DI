<?php

declare(strict_types=1);

namespace DI\Test\UnitTest\Definition\Source\Fixtures;

use DI\Attribute\Inject;

class AttributeFixturePromotedProperty
{
    public function __construct(#[Inject("foo")] public $promotedProperty) {
    }
}
