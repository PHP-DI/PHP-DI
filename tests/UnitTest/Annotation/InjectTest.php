<?php

declare(strict_types=1);

namespace DI\Test\UnitTest\Annotation;

use DI\Annotation\Inject;
use DI\Definition\Source\AnnotationBasedAutowiring;
use DI\Test\UnitTest\Annotation\Fixtures\InjectFixture;
use DI\Test\UnitTest\Annotation\Fixtures\MixedAnnotationsFixture;
use DI\Test\UnitTest\Annotation\Fixtures\NonImportedInjectFixture;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use DI\Definition\Exception\InvalidAnnotation;

/**
 * Inject annotation test class.
 *
 * @covers \DI\Annotation\Inject
 */
class InjectTest extends TestCase
{
    private \Doctrine\Common\Annotations\Reader $annotationReader;

    private ReflectionClass $reflectionClass;

    public function setUp(): void
    {
        $definitionReader = new AnnotationBasedAutowiring();
        $this->annotationReader = $definitionReader->getAnnotationReader();
        $this->reflectionClass = new ReflectionClass(InjectFixture::class);
    }

    public function testProperty1()
    {
        $property = $this->reflectionClass->getProperty('property1');
        /** @var $annotation Inject */
        $annotation = $this->annotationReader->getPropertyAnnotation($property, Inject::class);

        $this->assertInstanceOf(Inject::class, $annotation);
        $this->assertEquals('foo', $annotation->getName());
    }

    public function testProperty2()
    {
        $property = $this->reflectionClass->getProperty('property2');
        /** @var $annotation Inject */
        $annotation = $this->annotationReader->getPropertyAnnotation($property, Inject::class);

        $this->assertInstanceOf(Inject::class, $annotation);
        $this->assertNull($annotation->getName());
    }

    public function testProperty3()
    {
        $property = $this->reflectionClass->getProperty('property3');
        /** @var $annotation Inject */
        $annotation = $this->annotationReader->getPropertyAnnotation($property, Inject::class);

        $this->assertInstanceOf(Inject::class, $annotation);
        $this->assertEquals('foo', $annotation->getName());
    }

    public function testMethod1()
    {
        $method = $this->reflectionClass->getMethod('method1');
        /** @var $annotation Inject */
        $annotation = $this->annotationReader->getMethodAnnotation($method, Inject::class);

        $this->assertInstanceOf(Inject::class, $annotation);
        $this->assertEmpty($annotation->getParameters());
    }

    public function testMethod2()
    {
        $method = $this->reflectionClass->getMethod('method2');
        /** @var $annotation Inject */
        $annotation = $this->annotationReader->getMethodAnnotation($method, Inject::class);
        $parameters = $annotation->getParameters();

        $this->assertInstanceOf(Inject::class, $annotation);
        $this->assertCount(2, $parameters);
        $this->assertEquals('foo', $parameters[0]);
        $this->assertEquals('bar', $parameters[1]);
    }

    public function testMethod3()
    {
        $method = $this->reflectionClass->getMethod('method3');
        /** @var $annotation Inject */
        $annotation = $this->annotationReader->getMethodAnnotation($method, Inject::class);
        $parameters = $annotation->getParameters();

        $this->assertInstanceOf(Inject::class, $annotation);
        $this->assertCount(1, $parameters);

        $this->assertArrayHasKey('str1', $parameters);
        $this->assertEquals('foo', $parameters['str1']);
    }

    public function testInvalidAnnotation()
    {
        $this->expectException(InvalidAnnotation::class);
        $this->expectExceptionMessage('@Inject({"param" = "value"}) expects "value" to be a string, [] given.');
        $method = $this->reflectionClass->getMethod('method4');
        $this->annotationReader->getMethodAnnotation($method, Inject::class);
    }

    /**
     * Inject annotation should work even if not imported.
     */
    public function testNonImportedAnnotation()
    {
        $class = new ReflectionClass(NonImportedInjectFixture::class);
        $property = $class->getProperty('property1');
        /** @var $annotation Inject */
        $annotation = $this->annotationReader->getPropertyAnnotation($property, Inject::class);

        $this->assertInstanceOf(Inject::class, $annotation);
    }

    /**
     * Inject annotation should work even if there are other weird annotations in the file.
     */
    public function testMixedAnnotations()
    {
        $class = new ReflectionClass(MixedAnnotationsFixture::class);
        $property = $class->getProperty('property1');
        /** @var $annotation Inject */
        $annotation = $this->annotationReader->getPropertyAnnotation($property, Inject::class);

        $this->assertInstanceOf(Inject::class, $annotation);
    }
}
