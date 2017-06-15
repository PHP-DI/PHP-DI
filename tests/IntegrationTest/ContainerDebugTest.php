<?php

declare(strict_types=1);

namespace DI\Test\IntegrationTest;

use DI\Container;
use DI\ContainerBuilder;

/**
 * Tests container debugging.
 */
class ContainerDebugTest extends BaseContainerTest
{
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
            'create' => \DI\create(Container::class),
            'autowire' => \DI\autowire(Container::class),
            'factory' => \DI\factory(function () {
                return true;
            }),
            'callback' => function () {
                return true;
            },
            'decorator' => \DI\decorate(function () {
                return true;
            }),
            'alias' => \DI\get('value'),
            'environment' => \DI\env('foo'),
            'array' => \DI\add(['foo', 'bar']),
            'string' => \DI\string('foo'),
            'float' => \DI\value(1.5),
            'bool' => \DI\value(true),
            'str' => \DI\value('string'),
            'null' => \DI\value(null),
        ]);

        /** @var \DI\Container $container */
        $container = $builder->build();

        $container->set('entry_object', new \stdClass());
        $container->set('entry_array', ['foo', 'bar']);
        $container->set('entry_int', 100);
        $container->set('entry_bool', false);
        $container->set('entry_str', 'string');
        $container->set('entry_null', null);
        $container->set('entry_resource', fopen(__FILE__, 'rb'));
        $container->set('entry_callback', function () {
            return true;
        });

        // Default definitions
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

        // Container definitions
        $this->assertRegExp('/^Object \(\n {4}class = DI\\\Container\n/', $container->debugEntry('create'));
        $this->assertRegExp('/^Object \(\n {4}class = DI\\\Container\n/', $container->debugEntry('autowire'));
        $this->assertEquals('Factory', $container->debugEntry('factory'));
        $this->assertEquals('Factory', $container->debugEntry('callback'));
        $this->assertEquals('Decorate(decorator)', $container->debugEntry('decorator'));
        $this->assertEquals('get(value)', $container->debugEntry('alias'));
        $this->assertRegExp('/^Environment variable \(\n {4}variable = foo\n/', $container->debugEntry('environment'));
        $this->assertEquals("[\n    0 => 'foo',\n    1 => 'bar',\n]", $container->debugEntry('array'));
        $this->assertEquals('foo', $container->debugEntry('string'));
        $this->assertEquals('Value (1.5)', $container->debugEntry('float'));
        $this->assertEquals('Value (true)', $container->debugEntry('bool'));
        $this->assertEquals("Value ('string')", $container->debugEntry('str'));
        $this->assertEquals('Value (NULL)', $container->debugEntry('null'));

        // Container entries
        $this->assertEquals("Object (\n    class = stdClass\n)", $container->debugEntry('entry_object'));
        $this->assertEquals("[\n    0 => 'foo',\n    1 => 'bar',\n]", $container->debugEntry('array'));
        $this->assertEquals('Value (100)', $container->debugEntry('entry_int'));
        $this->assertEquals('Value (false)', $container->debugEntry('entry_bool'));
        $this->assertEquals("Value ('string')", $container->debugEntry('entry_str'));
        $this->assertEquals("Value (NULL)", $container->debugEntry('entry_null'));
        $this->assertEquals('Value (Resource)', $container->debugEntry('entry_resource'));
        $this->assertEquals('Factory', $container->debugEntry('entry_callback'));
    }
}
