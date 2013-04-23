<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.io/PHP-DI/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace UnitTests\DI\Definition\Source;

use DI\Definition\Source\ReflectionDefinitionSource;

/**
 * Test class for ReflectionDefinitionSource
 */
class ReflectionDefinitionSourceTest extends \PHPUnit_Framework_TestCase
{

    public function testUnknownClass()
    {
        $source = new ReflectionDefinitionSource();
        $this->assertNull($source->getDefinition('foo'));
    }

    public function testFixtureClass()
    {
        $source = new ReflectionDefinitionSource();
        $definition = $source->getDefinition('UnitTests\DI\Definition\Fixtures\ReflectionFixture');
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
