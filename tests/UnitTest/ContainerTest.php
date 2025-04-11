<?php

declare(strict_types=1);

namespace DI\Test\UnitTest;

use DI\Container;
use PHPUnit\Framework\TestCase;

/**
 * Test class for DI\Container.
 */
class ContainerTest extends TestCase
{
    /**
     * @test
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function canBeBuiltWithoutParameters()
    {
        self::assertInstanceOf(Container::class, new Container); // Should not be an error
    }
    /**
     * @test
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function canBeBuiltWithDefinitionArray()
    {
        $container = new Container([
            'foo' => 'bar',
        ]);
        self::assertInstanceOf(Container::class, $container);
        self::assertEquals('bar', $container->get('foo'));
    }
}
