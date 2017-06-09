<?php

declare(strict_types=1);

namespace DI\Test\UnitTest;

use DI\Container;
use DI\ContainerBuilder;
use DI\Test\UnitTest\Fixtures\PassByReferenceDependency;
use stdClass;

/**
 * Test class for Container.
 *
 * @covers \DI\Container
 */
class ContainerGetTest extends \PHPUnit_Framework_TestCase
{
    public function testSetGet()
    {
        $container = ContainerBuilder::buildDevContainer();
        $dummy = new stdClass();
        $container->set('key', $dummy);
        $this->assertSame($dummy, $container->get('key'));
    }

    /**
     * @expectedException \DI\NotFoundException
     */
    public function testGetNotFound()
    {
        $container = ContainerBuilder::buildDevContainer();
        $container->get('key');
    }

    public function testClosureIsResolved()
    {
        $closure = function () {
            return 'hello';
        };
        $container = ContainerBuilder::buildDevContainer();
        $container->set('key', $closure);
        $this->assertEquals('hello', $container->get('key'));
    }

    public function testGetWithClassName()
    {
        $container = ContainerBuilder::buildDevContainer();
        $this->assertInstanceOf('stdClass', $container->get('stdClass'));
    }

    public function testGetResolvesEntryOnce()
    {
        $container = ContainerBuilder::buildDevContainer();
        $this->assertSame($container->get('stdClass'), $container->get('stdClass'));
    }

    /**
     * Tests a class can be initialized with a parameter passed by reference.
     */
    public function testPassByReferenceParameter()
    {
        $container = ContainerBuilder::buildDevContainer();
        $object = $container->get(PassByReferenceDependency::class);
        $this->assertInstanceOf(PassByReferenceDependency::class, $object);
    }

    public function testKnownEntries()
    {
        $expectedEntries = [
            'DI\Container',
            'DI\FactoryInterface',
            'DI\InvokerInterface',
            'Psr\Container\ContainerInterface',
            'bar',
            'foo',
        ];

        $builder = new ContainerBuilder();
        $builder->addDefinitions(['foo' => 'bar']);

        /** @var \DI\Container $container */
        $container = $builder->build();
        $container->set('bar', 'baz');

        $this->assertEquals($expectedEntries, $container->getKnownEntryNames());
    }

    public function testEntriesDefinitions()
    {
        $builder = new ContainerBuilder();

        $builder->addDefinitions([
            'value' => \DI\value('foo'),
            'create' => \DI\create(Container::class),
            'autowire' => \DI\autowire( Container::class),
            'factory' => \DI\factory(function () {
                return true;
            }),
            'decorator' => \DI\decorate(function () {
                return true;
            }),
            'alias' => \DI\get('value'),
            'environment' => \DI\env('foo'),
            'array' => \DI\add(['foo']),
            'string' => \DI\string('foo'),
        ]);

        /** @var \DI\Container $container */
        $container = $builder->build();

        $container->set('entry', 'value');
        $container->set('object', new \stdClass());

        $this->assertRegExp('/^Object \(\n {4}class = DI\\\Container\n/', $container->debugEntry('DI\Container'));
        $this->assertRegExp(
            '/^Object \(\n {4}class = #NOT INSTANTIABLE# DI\\\FactoryInterface\n/',
            $container->debugEntry('DI\FactoryInterface')
        );
        $this->assertRegExp(
            '/^Object \(\n {4}class = #NOT INSTANTIABLE# DI\\\InvokerInterface\n/',
            $container->debugEntry('DI\InvokerInterface')
        );
        $this->assertRegExp(
            '/^Object \(\n {4}class = #NOT INSTANTIABLE# Psr\\\Container\\\ContainerInterface\n/',
            $container->debugEntry('Psr\Container\ContainerInterface')
        );

        $this->assertEquals("Value ('foo')", $container->debugEntry('value'));
        $this->assertRegExp('/^Object \(\n {4}class = DI\\\Container\n/', $container->debugEntry('create'));
        $this->assertRegExp('/^Object \(\n {4}class = DI\\\Container\n/', $container->debugEntry('autowire'));
        $this->assertEquals('Factory', $container->debugEntry('factory'));
        $this->assertEquals('Decorate(decorator)', $container->debugEntry('decorator'));
        $this->assertEquals('get(value)', $container->debugEntry('alias'));
        $this->assertRegExp('/^Environment variable \(\n {4}variable = foo\n/', $container->debugEntry('environment'));
        $this->assertEquals("[\n    0 => 'foo',\n]", $container->debugEntry('array'));
        $this->assertEquals('foo', $container->debugEntry('string'));
        $this->assertEquals('string', $container->debugEntry('entry'));
        $this->assertEquals('stdClass', $container->debugEntry('object'));
    }
}
