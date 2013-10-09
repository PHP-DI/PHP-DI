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
use DI\Definition\UndefinedInjection;
use UnitTests\DI\Definition\Fixtures\ReflectionFixture;

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
        $definition = $source->getDefinition(ReflectionFixture::class);
        $this->assertInstanceOf('DI\Definition\Definition', $definition);

        $constructorInjection = $definition->getConstructorInjection();
        $this->assertInstanceOf('DI\Definition\MethodInjection', $constructorInjection);

        $parameters = $constructorInjection->getParameters();
        $this->assertCount(3, $parameters);

        $param1 = $parameters[0];
        $this->assertEquals(new EntryReference(ReflectionFixture::class), $param1);

        $param2 = $parameters[1];
        $this->assertEquals(new UndefinedInjection(), $param2);

        $param3 = $parameters[2];
        $this->assertEquals(new UndefinedInjection(), $param3);
    }
}
