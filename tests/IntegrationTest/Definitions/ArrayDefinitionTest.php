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
                \DI\link('singleton'),
                \DI\link('prototype'),
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
                \DI\link('foo'),
            )),
            'foo' => \DI\object('stdClass'),

        ));
        $container = $builder->build();

        $array = $container->get('values');

        $this->assertCount(4, $array);
        $this->assertEquals('value 1', $array[0]);
        $this->assertEquals('value 2', $array[1]);
        $this->assertEquals('another value', $array[2]);
        $this->assertTrue($array[3] instanceof \stdClass);
    }
}
