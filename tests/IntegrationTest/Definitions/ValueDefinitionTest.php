<?php

namespace DI\Test\IntegrationTest\Definitions;

use DI\Container;
use DI\ContainerBuilder;
use stdClass;

/**
 * Test value definitions.
 *
 * @coversNothing
 */
class ValueDefinitionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Container
     */
    private $container;

    public function setUp()
    {
        $builder = new ContainerBuilder();
        $builder->addDefinitions([
            'string'  => 'foo',
            'int'     => 123,
            'object'  => new stdClass(),
            'helper'  => \DI\value('foo'),
            'closure' => \DI\value(function () {
                return 'foo';
            }),
        ]);

        $this->container = $builder->build();
    }

    public function test_value_definitions()
    {
        $this->assertEquals('foo', $this->container->get('string'));
        $this->assertEquals(123, $this->container->get('int'));
        $this->assertEquals(new \stdClass(), $this->container->get('object'));
        $this->assertEquals('foo', $this->container->get('helper'));
        $this->assertEquals('foo', call_user_func($this->container->get('closure')));
    }
}
