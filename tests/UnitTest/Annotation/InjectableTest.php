<?php

declare(strict_types=1);

namespace DI\Test\UnitTest\Annotation;

use DI\Annotation\Injectable;
use DI\Definition\Source\AnnotationBasedAutowiring;
use DI\Test\UnitTest\Annotation\Fixtures\Injectable1;
use DI\Test\UnitTest\Annotation\Fixtures\Injectable2;
use Doctrine\Common\Annotations\AnnotationReader as DoctrineAnnotationReader;
use ReflectionClass;

/**
 * Injectable annotation test class.
 *
 * @covers \DI\Annotation\Injectable
 */
class InjectableTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DoctrineAnnotationReader
     */
    private $annotationReader;

    public function setUp()
    {
        $definitionReader = new AnnotationBasedAutowiring();
        $this->annotationReader = $definitionReader->getAnnotationReader();
    }

    public function testEmptyAnnotation()
    {
        $class = new ReflectionClass(Injectable1::class);
        /** @var $annotation Injectable */
        $annotation = $this->annotationReader->getClassAnnotation($class, Injectable::class);

        $this->assertInstanceOf(Injectable::class, $annotation);
        $this->assertNull($annotation->isLazy());
    }

    public function testLazy()
    {
        $class = new ReflectionClass(Injectable2::class);
        /** @var $annotation Injectable */
        $annotation = $this->annotationReader->getClassAnnotation($class, Injectable::class);

        $this->assertInstanceOf(Injectable::class, $annotation);
        $this->assertTrue($annotation->isLazy());
    }
}
