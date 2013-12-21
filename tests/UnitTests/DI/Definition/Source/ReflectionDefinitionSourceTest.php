<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.io/PHP-DI/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace UnitTests\DI\Definition\Source;

use DI\Definition\EntryReference;
use DI\Definition\Source\ReflectionDefinitionSource;

/**
 * @covers \DI\Definition\Source\ReflectionDefinitionSource
 */
class ReflectionDefinitionSourceTest extends \PHPUnit_Framework_TestCase
{
    public function testUnknownClass()
    {
        $source = new ReflectionDefinitionSource();
        $this->assertNull($source->getDefinition('foo'));
    }

    public function testConstructor()
    {
        $source = new ReflectionDefinitionSource();
        $definition = $source->getDefinition('UnitTests\DI\Definition\Source\Fixtures\ReflectionFixture');
        $this->assertInstanceOf('DI\Definition\ClassDefinition', $definition);

        $constructorInjection = $definition->getConstructorInjection();
        $this->assertInstanceOf('DI\Definition\ClassDefinition\MethodInjection', $constructorInjection);

        $parameters = $constructorInjection->getParameters();
        $this->assertCount(1, $parameters);

        $param1 = $parameters[0];
        $this->assertEquals(new EntryReference('UnitTests\DI\Definition\Source\Fixtures\ReflectionFixture'), $param1);
    }

    public function testConstructorInParentClass()
    {
        $source = new ReflectionDefinitionSource();
        $definition = $source->getDefinition('UnitTests\DI\Definition\Source\Fixtures\ReflectionFixtureChild');
        $this->assertInstanceOf('DI\Definition\ClassDefinition', $definition);

        $constructorInjection = $definition->getConstructorInjection();
        $this->assertInstanceOf('DI\Definition\ClassDefinition\MethodInjection', $constructorInjection);

        $parameters = $constructorInjection->getParameters();
        $this->assertCount(1, $parameters);

        $param1 = $parameters[0];
        $this->assertEquals(new EntryReference('UnitTests\DI\Definition\Source\Fixtures\ReflectionFixture'), $param1);
    }
}
