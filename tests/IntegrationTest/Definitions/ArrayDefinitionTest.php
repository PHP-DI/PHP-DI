<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Test\IntegrationTest\Definitions;

use DI\ContainerBuilder;
use DI\Scope;

/**
 * Test array definitions
 *
 * @coversNothing
 */
class ArrayDefinitionTest extends \PHPUnit_Framework_TestCase
{
    public function test_array_with_values()
    {
        $builder = new ContainerBuilder();
        $builder->addDefinitions(array(
            'values' => array(
                'value 1',
                'value 2',
            ),
        ));
        $container = $builder->build();

        $array = $container->get('values');

        $this->assertEquals('value 1', $array[0]);
        $this->assertEquals('value 2', $array[1]);
    }

    public function test_array_with_links()
    {
        $builder = new ContainerBuilder();
        $builder->addDefinitions(array(
            'links'     => array(
                \DI\get('singleton'),
                \DI\get('prototype'),
            ),
            'singleton' => \DI\object('stdClass'),
            'prototype' => \DI\object('stdClass')
                ->scope(Scope::PROTOTYPE()),
        ));
        $container = $builder->build();

        $array = $container->get('links');

        $this->assertTrue($array[0] instanceof \stdClass);
        $this->assertTrue($array[1] instanceof \stdClass);

        $singleton = $container->get('singleton');
        $prototype = $container->get('prototype');

        $this->assertSame($singleton, $array[0]);
        $this->assertNotSame($prototype, $array[1]);
    }

    public function test_array_with_nested_definitions()
    {
        $builder = new ContainerBuilder();
        $builder->addDefinitions(array(
            'array' => array(
                \DI\env('PHP_DI_DO_NOT_DEFINE_THIS', 'env'),
                \DI\object('stdClass'),
            ),
        ));
        $container = $builder->build();

        $array = $container->get('array');

        $this->assertEquals('env', $array[0]);
        $this->assertEquals(new \stdClass, $array[1]);
    }

    /**
     * An array entry is a singleton
     */
    public function test_array_with_prototype_entries()
    {
        $builder = new ContainerBuilder();
        $builder->addDefinitions(array(
            'array'     => array(
                \DI\get('prototype'),
            ),
            'prototype' => \DI\object('stdClass')
                ->scope(Scope::PROTOTYPE()),
        ));
        $container = $builder->build();

        $array1 = $container->get('array');
        $array2 = $container->get('array');

        $this->assertSame($array1[0], $array2[0]);
    }

    public function test_add_entries()
    {
        $builder = new ContainerBuilder();
        $builder->addDefinitions(array(
            'values' => array(
                'value 1',
                'value 2',
            ),
        ));
        $builder->addDefinitions(array(
            'values' => \DI\add(array(
                'another value',
                \DI\get('foo'),
            )),
            'foo'    => \DI\object('stdClass'),
        ));
        $container = $builder->build();

        $array = $container->get('values');

        $this->assertCount(4, $array);
        $this->assertEquals('value 1', $array[0]);
        $this->assertEquals('value 2', $array[1]);
        $this->assertEquals('another value', $array[2]);
        $this->assertTrue($array[3] instanceof \stdClass);
    }

    public function test_add_entries_with_nested_definitions()
    {
        $builder = new ContainerBuilder();
        $builder->addDefinitions(array(
            'array' => array(
                \DI\env('PHP_DI_DO_NOT_DEFINE_THIS', 'env'),
                \DI\object('stdClass'),
            ),
        ));
        $builder->addDefinitions(array(
            'array' => \DI\add(array(
                \DI\env('PHP_DI_DO_NOT_DEFINE_THIS', 'foo'),
                \DI\object('stdClass'),
            )),
        ));
        $container = $builder->build();

        $array = $container->get('array');

        $this->assertEquals('env', $array[0]);
        $this->assertEquals(new \stdClass, $array[1]);
        $this->assertEquals('foo', $array[2]);
        $this->assertEquals(new \stdClass, $array[3]);
    }
}
