<?php

namespace DI\Test\UnitTest\Definition\Dumper;

use DI\Definition\DecoratorDefinition;
use DI\Definition\Dumper\DecoratorDefinitionDumper;
use DI\Definition\ValueDefinition;

/**
 * @covers \DI\Definition\Dumper\DecoratorDefinitionDumper
 */
class DecoratorDefinitionDumperTest extends \PHPUnit_Framework_TestCase
{
    public function testDump()
    {
        $definition = new DecoratorDefinition('foo', 'bar');
        $dumper = new DecoratorDefinitionDumper();

        $this->assertEquals('Decorate(foo)', $dumper->dump($definition));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage This definition dumper is only compatible with DecoratorDefinition objects, DI\Definition\ValueDefinition given
     */
    public function testInvalidDefinitionType()
    {
        $definition = new ValueDefinition('foo', 'bar');
        $dumper = new DecoratorDefinitionDumper();

        $dumper->dump($definition);
    }
}
