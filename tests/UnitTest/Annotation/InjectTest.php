<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Test\UnitTest\Annotation;

use DI\Annotation\Inject;
use DI\Definition\Source\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationReader as DoctrineAnnotationReader;
use ReflectionClass;

/**
 * Inject annotation test class
 *
 * @covers \DI\Annotation\Inject
 */
class InjectTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DoctrineAnnotationReader
     */
    private $annotationReader;

    /**
     * @var ReflectionClass
     */
    private $reflectionClass;

    public function setUp()
    {
        $definitionReader = new AnnotationReader();
        $this->annotationReader = $definitionReader->getAnnotationReader();
        $this->reflectionClass = new ReflectionClass('DI\Test\UnitTest\Annotation\Fixtures\InjectFixture');
    }

    public function testProperty1()
    {
        $property = $this->reflectionClass->getProperty('property1');
        /** @var $annotation Inject */
        $annotation = $this->annotationReader->getPropertyAnnotation($property, 'DI\Annotation\Inject');

        $this->assertInstanceOf('DI\Annotation\Inject', $annotation);
        $this->assertEquals('foo', $annotation->getName());
    }

    public function testProperty2()
    {
        $property = $this->reflectionClass->getProperty('property2');
        /** @var $annotation Inject */
        $annotation = $this->annotationReader->getPropertyAnnotation($property, 'DI\Annotation\Inject');

        $this->assertInstanceOf('DI\Annotation\Inject', $annotation);
        $this->assertNull($annotation->getName());
    }

    public function testProperty3()
    {
        $property = $this->reflectionClass->getProperty('property3');
        /** @var $annotation Inject */
        $annotation = $this->annotationReader->getPropertyAnnotation($property, 'DI\Annotation\Inject');

        $this->assertInstanceOf('DI\Annotation\Inject', $annotation);
        $this->assertEquals('foo', $annotation->getName());
    }

    public function testMethod1()
    {
        $method = $this->reflectionClass->getMethod('method1');
        /** @var $annotation Inject */
        $annotation = $this->annotationReader->getMethodAnnotation($method, 'DI\Annotation\Inject');

        $this->assertInstanceOf('DI\Annotation\Inject', $annotation);
        $this->assertEmpty($annotation->getParameters());
    }

    public function testMethod2()
    {
        $method = $this->reflectionClass->getMethod('method2');
        /** @var $annotation Inject */
        $annotation = $this->annotationReader->getMethodAnnotation($method, 'DI\Annotation\Inject');
        $parameters = $annotation->getParameters();

        $this->assertInstanceOf('DI\Annotation\Inject', $annotation);
        $this->assertCount(2, $parameters);
        $this->assertEquals('foo', $parameters[0]);
        $this->assertEquals('bar', $parameters[1]);
    }

    public function testMethod3()
    {
        $method = $this->reflectionClass->getMethod('method3');
        /** @var $annotation Inject */
        $annotation = $this->annotationReader->getMethodAnnotation($method, 'DI\Annotation\Inject');
        $parameters = $annotation->getParameters();

        $this->assertInstanceOf('DI\Annotation\Inject', $annotation);
        $this->assertCount(1, $parameters);

        $this->assertArrayHasKey('str1', $parameters);
        $this->assertEquals('foo', $parameters['str1']);
    }

    /**
     * @expectedException \DI\Definition\Exception\AnnotationException
     * @expectedExceptionMessage @Inject({"param" = "value"}) expects "value" to be a string, [] given.
     */
    public function testInvalidAnnotation()
    {
        $method = $this->reflectionClass->getMethod('method4');
        $this->annotationReader->getMethodAnnotation($method, 'DI\Annotation\Inject');
    }

    /**
     * Inject annotation should work even if not imported
     */
    public function testNonImportedAnnotation()
    {
        $class = new ReflectionClass('DI\Test\UnitTest\Annotation\Fixtures\NonImportedInjectFixture');
        $property = $class->getProperty('property1');
        /** @var $annotation Inject */
        $annotation = $this->annotationReader->getPropertyAnnotation($property, 'DI\Annotation\Inject');

        $this->assertInstanceOf('DI\Annotation\Inject', $annotation);
    }

    /**
     * Inject annotation should work even if there are other weird annotations in the file
     */
    public function testMixedAnnotations()
    {
        $class = new ReflectionClass('DI\Test\UnitTest\Annotation\Fixtures\MixedAnnotationsFixture');
        $property = $class->getProperty('property1');
        /** @var $annotation Inject */
        $annotation = $this->annotationReader->getPropertyAnnotation($property, 'DI\Annotation\Inject');

        $this->assertInstanceOf('DI\Annotation\Inject', $annotation);
    }
}
