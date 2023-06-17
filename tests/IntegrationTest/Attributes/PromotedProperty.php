<?php

declare(strict_types=1);

namespace DI\Test\IntegrationTest\Attributes;

use DI\Attribute\Inject;

class PromotedProperty
{
    public function __construct(#[Inject(A::class)] public $promotedProperty)
    {
    }
}
