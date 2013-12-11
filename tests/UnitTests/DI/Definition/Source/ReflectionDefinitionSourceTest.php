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

class ReflectionDefinitionSourceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers \DI\Definition\Source\ReflectionDefinitionSource::getDefinition
     */
    public function testUnknownClass()
    {
        $source = new ReflectionDefinitionSource();
        $this->assertNull($source->getDefinition('foo'));
    }

    /**
     * @covers \DI\Definition\Source\ReflectionDefinitionSource::getDefinition
     * @covers \DI\Definition\Source\ReflectionDefinitionSource::getMethodInjection
     */
    public function testConstructor()
    {
        $source = new ReflectionDefinitionSource();
        $definition = $source->getDefinition('UnitTests\DI\Definition\Source\Fixtures\ReflectionFixture');
        $this->assertInstanceOf('DI\Definition\ClassDefinition', $definition);

        $constructorInjection = $definition->getConstructorInjection();
        $this->assertInstanceOf('DI\Definition\ClassInjection\MethodInjection', $constructorInjection);

        $parameters = $constructorInjection->getParameters();
        $this->assertCount(1, $parameters);

        $param1 = $parameters['param1'];
        $this->assertEquals(new EntryReference('UnitTests\DI\Definition\Source\Fixtures\ReflectionFixture'), $param1);
    }

    /**
     * @covers \DI\Definition\Source\ReflectionDefinitionSource::getDefinition
     */
    public function testConstructorInParentClass()
    {
        $source = new ReflectionDefinitionSource();
        $definition = $source->getDefinition('UnitTests\DI\Definition\Source\Fixtures\ReflectionFixtureChild');
        $this->assertInstanceOf('DI\Definition\ClassDefinition', $definition);

        $constructorInjection = $definition->getConstructorInjection();
        $this->assertInstanceOf('DI\Definition\ClassInjection\MethodInjection', $constructorInjection);

        $parameters = $constructorInjection->getParameters();
        $this->assertCount(1, $parameters);

        $param1 = $parameters['param1'];
        $this->assertEquals(new EntryReference('UnitTests\DI\Definition\Source\Fixtures\ReflectionFixture'), $param1);
    }

    /**
     * @covers \DI\Definition\Source\ReflectionDefinitionSource::getPropertyInjection
     */
    public function testGetPropertyInjection()
    {
        $property = $this->getMock('\ReflectionProperty', null, array(), '', false);

        $source = new ReflectionDefinitionSource();
        $this->assertNull($source->getPropertyInjection('foo', $property));
    }
}
