<?php

namespace DI\Test\UnitTest;

use DI\Container;

/**
 * Test class for DI\Container.
 */
class ContainerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function canBeBuiltWithoutParameters()
    {
        self::assertInstanceOf(Container::class, new Container); // Should not be an error
    }
}
