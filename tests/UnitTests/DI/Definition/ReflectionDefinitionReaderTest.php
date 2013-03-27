<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.com/PHP-DI/
 * @copyright 2012 Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace UnitTests\DI\Definition;

use DI\Definition\ReflectionDefinitionReader;

/**
 * Test class for ReflectionDefinitionReader
 */
class ReflectionDefinitionReaderTest extends \PHPUnit_Framework_TestCase
{

    public function testUnknownClass()
    {
        $reader = new ReflectionDefinitionReader();
        $this->assertNull($reader->getDefinition('foo'));
    }

    public function testFixtureClass()
    {
        $reader = new ReflectionDefinitionReader();
        $definition = $reader->getDefinition('UnitTests\DI\Definition\Fixtures\ReflectionFixture');
        $this->assertInstanceOf('DI\Definition\Definition', $definition);

        $constructorInjection = $definition->getConstructorInjection();
        $this->assertInstanceOf('DI\Definition\MethodInjection', $constructorInjection);

        $parameterInjections = $constructorInjection->getParameterInjections();
        $this->assertCount(3, $parameterInjections);

        $param1 = $parameterInjections['param1'];
        $this->assertEquals('param1', $param1->getParameterName());
        $this->assertEquals('UnitTests\DI\Definition\Fixtures\ReflectionFixture', $param1->getEntryName());

        $param2 = $parameterInjections['param2'];
        $this->assertEquals('param2', $param2->getParameterName());
        $this->assertNull($param2->getEntryName());

        $param3 = $parameterInjections['param3'];
        $this->assertEquals('param3', $param3->getParameterName());
        $this->assertNull($param3->getEntryName());
    }

}
