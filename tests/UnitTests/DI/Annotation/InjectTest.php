<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.io/PHP-DI/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace UnitTests\DI\Annotation;

use DI\Annotation\Inject;
use DI\Definition\Source\AnnotationDefinitionSource;
use Doctrine\Common\Annotations\AnnotationReader;
use ReflectionClass;
use UnitTests\DI\Annotation\Fixtures\InjectFixture;
use UnitTests\DI\Annotation\Fixtures\MixedAnnotationsFixture;
use UnitTests\DI\Annotation\Fixtures\NonImportedInjectFixture;

/**
 * Inject annotation test class
 */
class InjectTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AnnotationReader
     */
    private $annotationReader;

    /**
     * @var ReflectionClass
     */
    private $reflectionClass;

    public function setUp()
    {
        $definitionReader = new AnnotationDefinitionSource();
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

    /**
     * Inject annotation should work even if not imported
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
     * Inject annotation should work even if there are other weird annotations in the file
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
