<?php

declare(strict_types=1);

namespace DI\Test\UnitTest\Definition\Source\Fixtures;

/**
 * Fixture class for the ReflectionBasedAutowiring tests.
 */
class AutowiringFixture
{
    public function __construct(AutowiringFixture $param1, $param2, $param3 = null)
    {
    }
}
