<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Test\UnitTest\Reflection;

use DI\Reflection\CallableReflectionFactory;

/**
 * @covers \DI\Reflection\CallableReflectionFactory
 */
class CallableReflectionFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function should_support_array_callable()
    {
        $reflection = CallableReflectionFactory::fromCallable([new TestClass, 'foo']);
        $this->assertInstanceOf('ReflectionMethod', $reflection);
    }

    /**
     * @test
     */
    public function should_support_closures()
    {
        /** @var \ReflectionFunction $reflection */
        $reflection = CallableReflectionFactory::fromCallable(function () {});
        $this->assertInstanceOf('ReflectionFunction', $reflection);
        $this->assertTrue($reflection->isClosure());
    }

    /**
     * @test
     */
    public function should_support_callable_objects()
    {
        /** @var \ReflectionMethod $reflection */
        $reflection = CallableReflectionFactory::fromCallable(new CallableTestClass());
        $this->assertInstanceOf('ReflectionMethod', $reflection);
        $this->assertEquals('DI\Test\UnitTest\Reflection\CallableTestClass', $reflection->getDeclaringClass()->getName());
        $this->assertEquals('__invoke', $reflection->getName());
    }

    /**
     * @test
     */
    public function should_support_callable_classes()
    {
        /** @var \ReflectionMethod $reflection */
        $reflection = CallableReflectionFactory::fromCallable('DI\Test\UnitTest\Reflection\CallableTestClass');
        $this->assertInstanceOf('ReflectionMethod', $reflection);
        $this->assertEquals('DI\Test\UnitTest\Reflection\CallableTestClass', $reflection->getDeclaringClass()->getName());
        $this->assertEquals('__invoke', $reflection->getName());
    }

    /**
     * @test
     */
    public function should_support_functions()
    {
        $reflection = CallableReflectionFactory::fromCallable('strlen');
        $this->assertInstanceOf('ReflectionFunction', $reflection);
    }
}

class TestClass
{
    public function foo()
    {
    }
}

class CallableTestClass
{
    public function __invoke()
    {
    }
}
