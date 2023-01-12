<?php

declare(strict_types=1);

namespace DI\Test\UnitTest\Attributes;

use DI\Attribute\Inject;
use DI\Test\UnitTest\Attributes\Fixtures\InjectFixture;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use DI\Definition\Exception\InvalidAttribute;

/**
 * Inject annotation test class.
 *
 * @requires PHP >= 8
 *
 * @covers \DI\Attribute\Inject
 */
class InjectTest extends TestCase
{
    private ReflectionClass $reflectionClass;

    public function setUp(): void
    {
        $this->reflectionClass = new ReflectionClass(InjectFixture::class);
    }

    public function testProperty1()
    {
        $property = $this->reflectionClass->getProperty('property1');
        /** @var Inject $annotation */
        $annotation = $property->getAttributes(Inject::class)[0]->newInstance();

        $this->assertInstanceOf(Inject::class, $annotation);
        $this->assertEquals('foo', $annotation->getName());
    }

    public function testProperty2()
    {
        $property = $this->reflectionClass->getProperty('property2');
        /** @var Inject $annotation */
        $annotation = $property->getAttributes(Inject::class)[0]->newInstance();

        $this->assertInstanceOf(Inject::class, $annotation);
        $this->assertNull($annotation->getName());
    }

    public function testProperty3()
    {
        $property = $this->reflectionClass->getProperty('property3');
        /** @var Inject $annotation */
        $annotation = $property->getAttributes(Inject::class)[0]->newInstance();

        $this->assertInstanceOf(Inject::class, $annotation);
        $this->assertEquals('foo', $annotation->getName());
    }

    public function testMethod1()
    {
        $method = $this->reflectionClass->getMethod('method1');
        /** @var Inject $annotation */
        $annotation = $method->getAttributes(Inject::class)[0]->newInstance();

        $this->assertInstanceOf(Inject::class, $annotation);
        $this->assertEmpty($annotation->getParameters());
    }

    public function testMethod2()
    {
        $method = $this->reflectionClass->getMethod('method2');
        /** @var Inject $annotation */
        $annotation = $method->getAttributes(Inject::class)[0]->newInstance();
        $parameters = $annotation->getParameters();

        $this->assertInstanceOf(Inject::class, $annotation);
        $this->assertCount(2, $parameters);
        $this->assertEquals('foo', $parameters[0]);
        $this->assertEquals('bar', $parameters[1]);
    }

    public function testMethod3()
    {
        $method = $this->reflectionClass->getMethod('method3');
        /** @var Inject $annotation */
        $annotation = $method->getAttributes(Inject::class)[0]->newInstance();
        $parameters = $annotation->getParameters();

        $this->assertInstanceOf(Inject::class, $annotation);
        $this->assertCount(1, $parameters);

        $this->assertArrayHasKey('str1', $parameters);
        $this->assertEquals('foo', $parameters['str1']);
    }

    public function testInvalidAnnotation()
    {
        $this->expectException(InvalidAttribute::class);
        $this->expectExceptionMessage("#[Inject(['param' => 'value'])] expects \"value\" to be a string, [] given.");
        $method = $this->reflectionClass->getMethod('method4');
        $method->getAttributes(Inject::class)[0]->newInstance();
    }
}
