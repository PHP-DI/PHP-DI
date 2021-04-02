<?php

declare(strict_types=1);

namespace DI\Test\UnitTest;

use DI\Container;
use DI\ContainerBuilder;
use DI\Test\UnitTest\Fixtures\PassByReferenceDependency;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * Test class for Container.
 *
 * @covers \DI\Container
 */
class ContainerGetTest extends TestCase
{
    public function testSetGet()
    {
        $container = new Container;
        $dummy = new stdClass();
        $container->set('key', $dummy);
        $this->assertSame($dummy, $container->get('key'));
    }

    public function testGetNotFound()
    {
        $this->expectException('DI\NotFoundException');
        $container = new Container;
        $container->get('key');
    }

    public function testClosureIsResolved()
    {
        $closure = function () {
            return 'hello';
        };
        $container = new Container;
        $container->set('key', $closure);
        $this->assertEquals('hello', $container->get('key'));
    }

    public function testGetWithClassName()
    {
        $container = new Container;
        $this->assertInstanceOf('stdClass', $container->get('stdClass'));
    }

    public function testGetResolvesEntryOnce()
    {
        $container = new Container;
        $this->assertSame($container->get('stdClass'), $container->get('stdClass'));
    }

    /**
     * Tests a class can be initialized with a parameter passed by reference.
     */
    public function testPassByReferenceParameter()
    {
        $container = new Container;
        $object = $container->get(PassByReferenceDependency::class);
        $this->assertInstanceOf(PassByReferenceDependency::class, $object);
    }
}
