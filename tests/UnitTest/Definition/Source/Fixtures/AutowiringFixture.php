<?php

namespace DI\Test\UnitTest\Definition\Source\Fixtures;

/**
 * Fixture class for the Autowiring tests
 */
class AutowiringFixture
{
    public function __construct(AutowiringFixture $param1, $param2, $param3 = null)
    {
    }
}
