<?php

declare(strict_types=1);

namespace DI\Test\UnitTest\Definition\Source;

use DI\Definition\Reference;
use DI\Definition\Definition;
use DI\Definition\ObjectDefinition;
use DI\Definition\ObjectDefinition\MethodInjection;
use DI\Definition\ObjectDefinition\PropertyInjection;
use DI\Definition\Source\AnnotationBasedAutowiring;
use DI\Test\UnitTest\Definition\Source\Fixtures\AnnotationFixture;
use DI\Test\UnitTest\Definition\Source\Fixtures\AnnotationFixture2;
use DI\Test\UnitTest\Definition\Source\Fixtures\AnnotationFixture3;
use DI\Test\UnitTest\Definition\Source\Fixtures\AnnotationFixture4;
use DI\Test\UnitTest\Definition\Source\Fixtures\AnnotationFixture5;
use DI\Test\UnitTest\Definition\Source\Fixtures\AnnotationFixtureChild;
use DI\Test\UnitTest\Definition\Source\Fixtures\AnnotationInjectableFixture;
use PHPUnit\Framework\TestCase;

/**
 * @covers \DI\Definition\Source\AnnotationBasedAutowiring
 */
class AnnotationBasedAutowiringTest extends TestCase
{
    public function testUnknownClass()
    {
        $this->assertNull((new AnnotationBasedAutowiring)->autowire('foo'));
    }

    public function testProperty1()
    {
        $definition = (new AnnotationBasedAutowiring)->autowire(AnnotationFixture::class);
        $this->assertInstanceOf(Definition::class, $definition);

        $properties = $definition->getPropertyInjections();
        $this->assertInstanceOf(PropertyInjection::class, $properties['property1']);

        $property = $properties['property1'];
        $this->assertEquals('property1', $property->getPropertyName());
        $this->assertEquals(new Reference('foo'), $property->getValue());
    }

    public function testUnannotatedProperty()
    {
        $definition = (new AnnotationBasedAutowiring)->autowire(AnnotationFixture::class);

        $this->assertNotHasPropertyInjection($definition, 'unannotatedProperty');
    }

    public function testStaticProperty()
    {
        $definition = (new AnnotationBasedAutowiring)->autowire(AnnotationFixture::class);

        $this->assertNotHasPropertyInjection($definition, 'staticProperty');
    }

    /**
     * @expectedException \DI\Definition\Exception\InvalidAnnotation
     * @expectedExceptionMessage @Inject found on property DI\Test\UnitTest\Definition\Source\Fixtures\AnnotationFixture4::property but unable to guess what to inject, use a @var annotation
     */
    public function testUnguessableProperty()
    {
        (new AnnotationBasedAutowiring)->autowire(AnnotationFixture4::class);
    }

    public function testConstructor()
    {
        $definition = (new AnnotationBasedAutowiring)->autowire(AnnotationFixture::class);
        $this->assertInstanceOf(Definition::class, $definition);

        $constructorInjection = $definition->getConstructorInjection();
        $this->assertInstanceOf(MethodInjection::class, $constructorInjection);

        $parameters = $constructorInjection->getParameters();
        $this->assertCount(2, $parameters);
        $this->assertEquals(new Reference('foo'), $parameters[0]);
        $this->assertEquals(new Reference('bar'), $parameters[1]);
    }

    public function testMethod1()
    {
        $definition = (new AnnotationBasedAutowiring)->autowire(AnnotationFixture::class);
        $this->assertInstanceOf(Definition::class, $definition);

        $methodInjection = $this->getMethodInjection($definition, 'method1');
        $this->assertInstanceOf(MethodInjection::class, $methodInjection);

        $this->assertEmpty($methodInjection->getParameters());
    }

    public function testMethod2()
    {
        $definition = (new AnnotationBasedAutowiring)->autowire(AnnotationFixture::class);
        $this->assertInstanceOf(Definition::class, $definition);

        $methodInjection = $this->getMethodInjection($definition, 'method2');
        $this->assertInstanceOf(MethodInjection::class, $methodInjection);

        $parameters = $methodInjection->getParameters();
        $this->assertCount(2, $parameters);
        $this->assertEquals(new Reference('foo'), $parameters[0]);
        $this->assertEquals(new Reference('bar'), $parameters[1]);
    }

    public function testMethod3()
    {
        $definition = (new AnnotationBasedAutowiring)->autowire(AnnotationFixture::class);
        $this->assertInstanceOf(Definition::class, $definition);

        $methodInjection = $this->getMethodInjection($definition, 'method3');
        $this->assertInstanceOf(MethodInjection::class, $methodInjection);

        $parameters = $methodInjection->getParameters();
        $this->assertCount(2, $parameters);

        $reference = new Reference(AnnotationFixture2::class);
        $this->assertEquals($reference, $parameters[0]);
        $this->assertEquals($reference, $parameters[1]);
    }

    public function testMethod4()
    {
        $definition = (new AnnotationBasedAutowiring)->autowire(AnnotationFixture::class);
        $this->assertInstanceOf(Definition::class, $definition);

        $methodInjection = $this->getMethodInjection($definition, 'method4');
        $this->assertInstanceOf(MethodInjection::class, $methodInjection);

        $parameters = $methodInjection->getParameters();
        $this->assertCount(2, $parameters);
        $this->assertEquals(new Reference('foo'), $parameters[0]);
        $this->assertEquals(new Reference('bar'), $parameters[1]);
    }

    public function testMethod5()
    {
        $definition = (new AnnotationBasedAutowiring)->autowire(AnnotationFixture::class);
        $this->assertInstanceOf(Definition::class, $definition);

        $methodInjection = $this->getMethodInjection($definition, 'method5');
        $this->assertInstanceOf(MethodInjection::class, $methodInjection);

        $parameters = $methodInjection->getParameters();
        $this->assertCount(1, $parameters);

        // Offset is 1, not 0, because parameter 0 wasn't defined
        $this->assertEquals(new Reference('bar'), $parameters[1]);
    }

    public function testUnannotatedMethod()
    {
        $definition = (new AnnotationBasedAutowiring)->autowire(AnnotationFixture::class);

        $this->assertNull($this->getMethodInjection($definition, 'unannotatedMethod'));
    }

    /**
     * @test
     */
    public function optionalParametersShouldBeIgnored()
    {
        $definition = (new AnnotationBasedAutowiring)->autowire(AnnotationFixture::class);

        $methodInjection = $this->getMethodInjection($definition, 'optionalParameter');

        $parameters = $methodInjection->getParameters();
        $this->assertCount(1, $parameters);

        $this->assertEquals(new Reference('foo'), $parameters[0]);
        $this->assertArrayNotHasKey(1, $parameters);
    }

    public function testStaticMethod()
    {
        $definition = (new AnnotationBasedAutowiring)->autowire(AnnotationFixture::class);

        $this->assertNull($this->getMethodInjection($definition, 'staticMethod'));
    }

    public function testInjectable()
    {
        $definition = (new AnnotationBasedAutowiring)->autowire(AnnotationInjectableFixture::class);
        $this->assertInstanceOf(Definition::class, $definition);
        $this->assertTrue($definition->isLazy());
    }

    /**
     * @see https://github.com/mnapoli/PHP-DI/issues/99
     */
    public function testIssue99()
    {
        $definition = (new AnnotationBasedAutowiring)->autowire(AnnotationFixture3::class);
        $this->assertInstanceOf(Definition::class, $definition);

        $methodInjection = $this->getMethodInjection($definition, 'method1');
        $this->assertInstanceOf(MethodInjection::class, $methodInjection);

        $parameters = $methodInjection->getParameters();
        $this->assertCount(1, $parameters);
        $this->assertEquals(
            new Reference(AnnotationFixture2::class),
            $parameters[0]
        );
    }

    /**
     * @see https://github.com/mnapoli/PHP-DI/issues/184
     * @expectedException \PhpDocReader\AnnotationException
     */
    public function testFailWithPhpDocErrors()
    {
        (new AnnotationBasedAutowiring)->autowire(AnnotationFixture5::class);
    }

    /**
     * @see https://github.com/mnapoli/PHP-DI/issues/184
     */
    public function testIgnorePhpDocErrors()
    {
        $source = new AnnotationBasedAutowiring($ignorePhpDocErrors = true);
        $source->autowire(AnnotationFixture5::class);
    }

    public function testMergedWithParentDefinition()
    {
        $definition = (new AnnotationBasedAutowiring)->autowire(AnnotationFixtureChild::class);

        $this->assertHasPropertyInjection($definition, 'propertyChild');
        $this->assertNotNull($this->getMethodInjection($definition, 'methodChild'));
        $this->assertHasPropertyInjection($definition, 'propertyParent');
        $this->assertNotNull($this->getMethodInjection($definition, 'methodParent'));
    }

    /**
     * It should read the private properties of the parent classes.
     *
     * @see https://github.com/mnapoli/PHP-DI/issues/257
     */
    public function testReadParentPrivateProperties()
    {
        $definition = (new AnnotationBasedAutowiring)->autowire(AnnotationFixtureChild::class);
        $this->assertHasPropertyInjection($definition, 'propertyParentPrivate');
    }

    private function getMethodInjection(ObjectDefinition $definition, $name)
    {
        $methodInjections = $definition->getMethodInjections();
        foreach ($methodInjections as $methodInjection) {
            if ($methodInjection->getMethodName() === $name) {
                return $methodInjection;
            }
        }

        return null;
    }

    private function assertHasPropertyInjection(ObjectDefinition $definition, $propertyName)
    {
        $propertyInjections = $definition->getPropertyInjections();
        foreach ($propertyInjections as $propertyInjection) {
            if ($propertyInjection->getPropertyName() === $propertyName) {
                return;
            }
        }
        $this->fail('No property injection found for ' . $propertyName);
    }

    private function assertNotHasPropertyInjection(ObjectDefinition $definition, $propertyName)
    {
        $propertyInjections = $definition->getPropertyInjections();
        foreach ($propertyInjections as $propertyInjection) {
            if ($propertyInjection->getPropertyName() === $propertyName) {
                $this->fail('No property injection found for ' . $propertyName);
            }
        }
    }
}
