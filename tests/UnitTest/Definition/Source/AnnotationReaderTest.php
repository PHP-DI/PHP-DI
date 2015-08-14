<?php

namespace DI\Test\UnitTest\Definition\Source;

use DI\Definition\AliasDefinition;
use DI\Definition\Definition;
use DI\Definition\ObjectDefinition;
use DI\Definition\ObjectDefinition\MethodInjection;
use DI\Definition\ObjectDefinition\PropertyInjection;
use DI\Definition\Source\AnnotationReader;
use DI\Scope;
use DI\Test\UnitTest\Definition\Source\Fixtures\AnnotationFixture;
use DI\Test\UnitTest\Definition\Source\Fixtures\AnnotationFixture2;
use DI\Test\UnitTest\Definition\Source\Fixtures\AnnotationFixture3;
use DI\Test\UnitTest\Definition\Source\Fixtures\AnnotationFixture4;
use DI\Test\UnitTest\Definition\Source\Fixtures\AnnotationFixture5;
use DI\Test\UnitTest\Definition\Source\Fixtures\AnnotationFixtureChild;
use DI\Test\UnitTest\Definition\Source\Fixtures\AnnotationInjectableFixture;

/**
 * @covers \DI\Definition\Source\AnnotationReader
 */
class AnnotationReaderTest extends \PHPUnit_Framework_TestCase
{
    public function testUnknownClass()
    {
        $this->assertNull((new AnnotationReader)->getDefinition('foo'));
    }

    public function testProperty1()
    {
        $definition = (new AnnotationReader)->getDefinition(AnnotationFixture::class);
        $this->assertInstanceOf(Definition::class, $definition);

        $properties = $definition->getPropertyInjections();
        $this->assertInstanceOf(PropertyInjection::class, $properties['property1']);

        $property = $properties['property1'];
        $this->assertEquals('property1', $property->getPropertyName());
        $this->assertEquals(new AliasDefinition('foo'), $property->getValue());
    }

    public function testUnannotatedProperty()
    {
        $definition = (new AnnotationReader)->getDefinition(AnnotationFixture::class);

        $this->assertNotHasPropertyInjection($definition, 'unannotatedProperty');
    }

    public function testStaticProperty()
    {
        $definition = (new AnnotationReader)->getDefinition(AnnotationFixture::class);

        $this->assertNotHasPropertyInjection($definition, 'staticProperty');
    }

    /**
     * @expectedException \DI\Definition\Exception\AnnotationException
     * @expectedExceptionMessage @Inject found on property DI\Test\UnitTest\Definition\Source\Fixtures\AnnotationFixture4::property but unable to guess what to inject, use a @var annotation
     */
    public function testUnguessableProperty()
    {
        (new AnnotationReader)->getDefinition(AnnotationFixture4::class);
    }

    public function testConstructor()
    {
        $definition = (new AnnotationReader)->getDefinition(AnnotationFixture::class);
        $this->assertInstanceOf(Definition::class, $definition);

        $constructorInjection = $definition->getConstructorInjection();
        $this->assertInstanceOf(MethodInjection::class, $constructorInjection);

        $parameters = $constructorInjection->getParameters();
        $this->assertCount(2, $parameters);
        $this->assertEquals(new AliasDefinition('foo'), $parameters[0]);
        $this->assertEquals(new AliasDefinition('bar'), $parameters[1]);
    }

    public function testMethod1()
    {
        $definition = (new AnnotationReader)->getDefinition(AnnotationFixture::class);
        $this->assertInstanceOf(Definition::class, $definition);

        $methodInjection = $this->getMethodInjection($definition, 'method1');
        $this->assertInstanceOf(MethodInjection::class, $methodInjection);

        $this->assertEmpty($methodInjection->getParameters());
    }

    public function testMethod2()
    {
        $definition = (new AnnotationReader)->getDefinition(AnnotationFixture::class);
        $this->assertInstanceOf(Definition::class, $definition);

        $methodInjection = $this->getMethodInjection($definition, 'method2');
        $this->assertInstanceOf(MethodInjection::class, $methodInjection);

        $parameters = $methodInjection->getParameters();
        $this->assertCount(2, $parameters);
        $this->assertEquals(new AliasDefinition('foo'), $parameters[0]);
        $this->assertEquals(new AliasDefinition('bar'), $parameters[1]);
    }

    public function testMethod3()
    {
        $definition = (new AnnotationReader)->getDefinition(AnnotationFixture::class);
        $this->assertInstanceOf(Definition::class, $definition);

        $methodInjection = $this->getMethodInjection($definition, 'method3');
        $this->assertInstanceOf(MethodInjection::class, $methodInjection);

        $parameters = $methodInjection->getParameters();
        $this->assertCount(2, $parameters);

        $reference = new AliasDefinition(AnnotationFixture2::class);
        $this->assertEquals($reference, $parameters[0]);
        $this->assertEquals($reference, $parameters[1]);
    }

    public function testMethod4()
    {
        $definition = (new AnnotationReader)->getDefinition(AnnotationFixture::class);
        $this->assertInstanceOf(Definition::class, $definition);

        $methodInjection = $this->getMethodInjection($definition, 'method4');
        $this->assertInstanceOf(MethodInjection::class, $methodInjection);

        $parameters = $methodInjection->getParameters();
        $this->assertCount(2, $parameters);
        $this->assertEquals(new AliasDefinition('foo'), $parameters[0]);
        $this->assertEquals(new AliasDefinition('bar'), $parameters[1]);
    }

    public function testMethod5()
    {
        $definition = (new AnnotationReader)->getDefinition(AnnotationFixture::class);
        $this->assertInstanceOf(Definition::class, $definition);

        $methodInjection = $this->getMethodInjection($definition, 'method5');
        $this->assertInstanceOf(MethodInjection::class, $methodInjection);

        $parameters = $methodInjection->getParameters();
        $this->assertCount(1, $parameters);

        // Offset is 1, not 0, because parameter 0 wasn't defined
        $this->assertEquals(new AliasDefinition('bar'), $parameters[1]);
    }

    public function testUnannotatedMethod()
    {
        $definition = (new AnnotationReader)->getDefinition(AnnotationFixture::class);

        $this->assertNull($this->getMethodInjection($definition, 'unannotatedMethod'));
    }

    /**
     * @test
     */
    public function optionalParametersShouldBeIgnored()
    {
        $definition = (new AnnotationReader)->getDefinition(AnnotationFixture::class);

        $methodInjection = $this->getMethodInjection($definition, 'optionalParameter');

        $parameters = $methodInjection->getParameters();
        $this->assertCount(1, $parameters);

        $this->assertEquals(new AliasDefinition('foo'), $parameters[0]);
        $this->assertArrayNotHasKey(1, $parameters);
    }

    public function testStaticMethod()
    {
        $definition = (new AnnotationReader)->getDefinition(AnnotationFixture::class);

        $this->assertNull($this->getMethodInjection($definition, 'staticMethod'));
    }

    public function testInjectable()
    {
        $definition = (new AnnotationReader)->getDefinition(AnnotationInjectableFixture::class);
        $this->assertInstanceOf(Definition::class, $definition);

        $this->assertEquals(Scope::PROTOTYPE, $definition->getScope());
        $this->assertTrue($definition->isLazy());
    }

    /**
     * @see https://github.com/mnapoli/PHP-DI/issues/99
     */
    public function testIssue99()
    {
        $definition = (new AnnotationReader)->getDefinition(AnnotationFixture3::class);
        $this->assertInstanceOf(Definition::class, $definition);

        $methodInjection = $this->getMethodInjection($definition, 'method1');
        $this->assertInstanceOf(MethodInjection::class, $methodInjection);

        $parameters = $methodInjection->getParameters();
        $this->assertCount(1, $parameters);
        $this->assertEquals(
            new AliasDefinition(AnnotationFixture2::class),
            $parameters[0]
        );
    }

    /**
     * @see https://github.com/mnapoli/PHP-DI/issues/184
     * @expectedException \PhpDocReader\AnnotationException
     */
    public function testFailWithPhpDocErrors()
    {
        (new AnnotationReader)->getDefinition(AnnotationFixture5::class);
    }

    /**
     * @see https://github.com/mnapoli/PHP-DI/issues/184
     */
    public function testIgnorePhpDocErrors()
    {
        $source = new AnnotationReader($ignorePhpDocErrors = true);
        $source->getDefinition(AnnotationFixture5::class);
    }

    public function testMergedWithParentDefinition()
    {
        $definition = (new AnnotationReader)->getDefinition(AnnotationFixtureChild::class);

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
        $definition = (new AnnotationReader)->getDefinition(AnnotationFixtureChild::class);
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
