<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Test\UnitTest\Definition\Source;

use DI\Definition\EntryReference;
use DI\Definition\FunctionCallDefinition;
use DI\Definition\Source\Autowiring;

/**
 * @covers \DI\Definition\Source\Autowiring
 */
class AutowiringTest extends \PHPUnit_Framework_TestCase
{
    public function testUnknownClass()
    {
        $source = new Autowiring();
        $this->assertNull($source->getDefinition('foo'));
    }

    public function testConstructor()
    {
        $source = new Autowiring();
        $definition = $source->getDefinition('DI\Test\UnitTest\Definition\Source\Fixtures\AutowiringFixture');
        $this->assertInstanceOf('DI\Definition\ObjectDefinition', $definition);

        $constructorInjection = $definition->getConstructorInjection();
        $this->assertInstanceOf('DI\Definition\ObjectDefinition\MethodInjection', $constructorInjection);

        $parameters = $constructorInjection->getParameters();
        $this->assertCount(1, $parameters);

        $param1 = $parameters[0];
        $this->assertEquals(new EntryReference('DI\Test\UnitTest\Definition\Source\Fixtures\AutowiringFixture'), $param1);
    }

    public function testConstructorInParentClass()
    {
        $source = new Autowiring();
        $definition = $source->getDefinition('DI\Test\UnitTest\Definition\Source\Fixtures\AutowiringFixtureChild');
        $this->assertInstanceOf('DI\Definition\ObjectDefinition', $definition);

        $constructorInjection = $definition->getConstructorInjection();
        $this->assertInstanceOf('DI\Definition\ObjectDefinition\MethodInjection', $constructorInjection);

        $parameters = $constructorInjection->getParameters();
        $this->assertCount(1, $parameters);

        $param1 = $parameters[0];
        $this->assertEquals(new EntryReference('DI\Test\UnitTest\Definition\Source\Fixtures\AutowiringFixture'), $param1);
    }

    public function testClosureDefinition()
    {
        $source = new Autowiring();

        $definition = $source->getCallableDefinition(function (\stdClass $foo, $bar) {
        });

        $this->assertTrue($definition instanceof FunctionCallDefinition);
        $this->assertCount(1, $definition->getParameters());
        $this->assertEquals(new EntryReference('stdClass'), $definition->getParameter(0));
    }

    public function testMethodCallDefinition()
    {
        $source = new Autowiring();

        $object = new TestClass();
        $definition = $source->getCallableDefinition(array($object, 'foo'));

        $this->assertTrue($definition instanceof FunctionCallDefinition);
        $this->assertCount(1, $definition->getParameters());
        $this->assertEquals(new EntryReference('stdClass'), $definition->getParameter(0));
    }

    /**
     * @test
     */
    public function optionalParameterShouldBeIgnored()
    {
        $source = new Autowiring();

        $object = new TestClass();
        $definition = $source->getCallableDefinition(array($object, 'optional'));

        $this->assertTrue($definition instanceof FunctionCallDefinition);
        $this->assertCount(0, $definition->getParameters());
    }

    /**
     * @test
     */
    public function callableObjectShouldWork()
    {
        $source = new Autowiring();

        $definition = $source->getCallableDefinition(new CallableTestClass());

        $this->assertTrue($definition instanceof FunctionCallDefinition);
        $this->assertCount(1, $definition->getParameters());
        $this->assertEquals(new EntryReference('stdClass'), $definition->getParameter(0));
    }
}

class TestClass
{
    public function foo(\stdClass $foo, $bar)
    {
    }

    public function optional(\stdClass $foo = null)
    {
    }
}

class CallableTestClass
{
    public function __invoke(\stdClass $foo)
    {
    }
}
