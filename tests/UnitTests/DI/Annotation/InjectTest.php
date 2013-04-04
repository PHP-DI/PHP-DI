<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.com/PHP-DI/
 * @copyright 2012 Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace UnitTests\DI\Annotation;

use DI\Annotation\Inject;
use DI\Container;
use DI\Definition\Source\AnnotationDefinitionSource;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use ReflectionClass;

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
        $this->reflectionClass = new ReflectionClass('UnitTests\DI\Annotation\Fixtures\InjectFixture');
    }

    public function testProperty1()
    {
        $property = $this->reflectionClass->getProperty('property1');
        /** @var $annotation Inject */
        $annotation = $this->annotationReader->getPropertyAnnotation($property, 'DI\Annotation\Inject');

        $this->assertInstanceOf('DI\Annotation\Inject', $annotation);
        $this->assertEquals('foo', $annotation->getName());
        $this->assertNull($annotation->isLazy());
    }

    public function testProperty2()
    {
        $property = $this->reflectionClass->getProperty('property2');
        /** @var $annotation Inject */
        $annotation = $this->annotationReader->getPropertyAnnotation($property, 'DI\Annotation\Inject');

        $this->assertInstanceOf('DI\Annotation\Inject', $annotation);
        $this->assertNull($annotation->getName());
        $this->assertNull($annotation->isLazy());
    }

    public function testProperty3()
    {
        $property = $this->reflectionClass->getProperty('property3');
        /** @var $annotation Inject */
        $annotation = $this->annotationReader->getPropertyAnnotation($property, 'DI\Annotation\Inject');

        $this->assertInstanceOf('DI\Annotation\Inject', $annotation);
        $this->assertEquals('foo', $annotation->getName());
        $this->assertTrue($annotation->isLazy());
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
        $this->assertEquals('foo', $parameters[0]['name']);
        $this->assertEquals('bar', $parameters[1]['name']);
    }

    public function testMethod3()
    {
        $method = $this->reflectionClass->getMethod('method3');
        /** @var $annotation Inject */
        $annotation = $this->annotationReader->getMethodAnnotation($method, 'DI\Annotation\Inject');
        $parameters = $annotation->getParameters();

        $this->assertInstanceOf('DI\Annotation\Inject', $annotation);
        $this->assertCount(2, $parameters);

        $this->assertEquals('foo', $parameters[0]['name']);
        $this->assertTrue($parameters[0]['lazy']);

        $this->assertEquals('bar', $parameters[1]['name']);
    }

    public function testMethod4()
    {
        $method = $this->reflectionClass->getMethod('method4');
        /** @var $annotation Inject */
        $annotation = $this->annotationReader->getMethodAnnotation($method, 'DI\Annotation\Inject');
        $parameters = $annotation->getParameters();

        $this->assertInstanceOf('DI\Annotation\Inject', $annotation);
        $this->assertCount(1, $parameters);

        $this->assertArrayHasKey('str2', $parameters);
        $this->assertEquals('foo', $parameters['str2']['name']);
    }

    public function testMethod5()
    {
        $method = $this->reflectionClass->getMethod('method5');
        /** @var $annotation Inject */
        $annotation = $this->annotationReader->getMethodAnnotation($method, 'DI\Annotation\Inject');
        $parameters = $annotation->getParameters();

        $this->assertInstanceOf('DI\Annotation\Inject', $annotation);
        $this->assertCount(1, $parameters);

        $this->assertArrayHasKey('str2', $parameters);
        $this->assertTrue($parameters['str2']['lazy']);
    }

}
