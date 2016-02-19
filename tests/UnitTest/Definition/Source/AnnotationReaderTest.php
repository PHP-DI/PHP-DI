<?php

namespace DI\Test\UnitTest\Definition\Source;

use DI\Definition\EntryReference;
use DI\Definition\ObjectDefinition;
use DI\Definition\Source\AnnotationReader;
use DI\Scope;

/**
 * @covers \DI\Definition\Source\AnnotationReader
 */
class AnnotationReaderTest extends \PHPUnit_Framework_TestCase
{
    public function testUnknownClass()
    {
        $source = new AnnotationReader();
        $this->assertNull($source->getDefinition('foo'));
    }

    public function testProperty1()
    {
        $source = new AnnotationReader();
        $definition = $source->getDefinition('DI\Test\UnitTest\Definition\Source\Fixtures\AnnotationFixture');
        $this->assertInstanceOf('DI\Definition\Definition', $definition);

        $properties = $definition->getPropertyInjections();
        $this->assertInstanceOf('DI\Definition\ObjectDefinition\PropertyInjection', $properties['property1']);

        $property = $properties['property1'];
        $this->assertEquals('property1', $property->getPropertyName());
        $this->assertEquals(new EntryReference('foo'), $property->getValue());
    }

    public function testUnannotatedProperty()
    {
        $source = new AnnotationReader();
        $definition = $source->getDefinition('DI\Test\UnitTest\Definition\Source\Fixtures\AnnotationFixture');

        $this->assertNotHasPropertyInjection($definition, 'unannotatedProperty');
    }

    public function testStaticProperty()
    {
        $source = new AnnotationReader();
        $definition = $source->getDefinition('DI\Test\UnitTest\Definition\Source\Fixtures\AnnotationFixture');

        $this->assertNotHasPropertyInjection($definition, 'staticProperty');
    }

    /**
     * @expectedException \DI\Definition\Exception\AnnotationException
     * @expectedExceptionMessage @Inject found on property DI\Test\UnitTest\Definition\Source\Fixtures\AnnotationFixture4::property but unable to guess what to inject, use a @var annotation
     */
    public function testUnguessableProperty()
    {
        $source = new AnnotationReader();
        $source->getDefinition('DI\Test\UnitTest\Definition\Source\Fixtures\AnnotationFixture4');
    }

    public function testConstructor()
    {
        $source = new AnnotationReader();
        $definition = $source->getDefinition('DI\Test\UnitTest\Definition\Source\Fixtures\AnnotationFixture');
        $this->assertInstanceOf('DI\Definition\Definition', $definition);

        $constructorInjection = $definition->getConstructorInjection();
        $this->assertInstanceOf('DI\Definition\ObjectDefinition\MethodInjection', $constructorInjection);

        $parameters = $constructorInjection->getParameters();
        $this->assertCount(2, $parameters);
        $this->assertEquals(new EntryReference('foo'), $parameters[0]);
        $this->assertEquals(new EntryReference('bar'), $parameters[1]);
    }

    public function testMethod1()
    {
        $source = new AnnotationReader();
        $definition = $source->getDefinition('DI\Test\UnitTest\Definition\Source\Fixtures\AnnotationFixture');
        $this->assertInstanceOf('DI\Definition\Definition', $definition);

        $methodInjection = $this->getMethodInjection($definition, 'method1');
        $this->assertInstanceOf('DI\Definition\ObjectDefinition\MethodInjection', $methodInjection);

        $this->assertEmpty($methodInjection->getParameters());
    }

    public function testMethod2()
    {
        $source = new AnnotationReader();
        $definition = $source->getDefinition('DI\Test\UnitTest\Definition\Source\Fixtures\AnnotationFixture');
        $this->assertInstanceOf('DI\Definition\Definition', $definition);

        $methodInjection = $this->getMethodInjection($definition, 'method2');
        $this->assertInstanceOf('DI\Definition\ObjectDefinition\MethodInjection', $methodInjection);

        $parameters = $methodInjection->getParameters();
        $this->assertCount(2, $parameters);
        $this->assertEquals(new EntryReference('foo'), $parameters[0]);
        $this->assertEquals(new EntryReference('bar'), $parameters[1]);
    }

    public function testMethod3()
    {
        $source = new AnnotationReader();
        $definition = $source->getDefinition('DI\Test\UnitTest\Definition\Source\Fixtures\AnnotationFixture');
        $this->assertInstanceOf('DI\Definition\Definition', $definition);

        $methodInjection = $this->getMethodInjection($definition, 'method3');
        $this->assertInstanceOf('DI\Definition\ObjectDefinition\MethodInjection', $methodInjection);

        $parameters = $methodInjection->getParameters();
        $this->assertCount(2, $parameters);

        $reference = new EntryReference('DI\Test\UnitTest\Definition\Source\Fixtures\AnnotationFixture2');
        $this->assertEquals($reference, $parameters[0]);
        $this->assertEquals($reference, $parameters[1]);
    }

    public function testMethod4()
    {
        $source = new AnnotationReader();
        $definition = $source->getDefinition('DI\Test\UnitTest\Definition\Source\Fixtures\AnnotationFixture');
        $this->assertInstanceOf('DI\Definition\Definition', $definition);

        $methodInjection = $this->getMethodInjection($definition, 'method4');
        $this->assertInstanceOf('DI\Definition\ObjectDefinition\MethodInjection', $methodInjection);

        $parameters = $methodInjection->getParameters();
        $this->assertCount(2, $parameters);
        $this->assertEquals(new EntryReference('foo'), $parameters[0]);
        $this->assertEquals(new EntryReference('bar'), $parameters[1]);
    }

    public function testMethod5()
    {
        $source = new AnnotationReader();
        $definition = $source->getDefinition('DI\Test\UnitTest\Definition\Source\Fixtures\AnnotationFixture');
        $this->assertInstanceOf('DI\Definition\Definition', $definition);

        $methodInjection = $this->getMethodInjection($definition, 'method5');
        $this->assertInstanceOf('DI\Definition\ObjectDefinition\MethodInjection', $methodInjection);

        $parameters = $methodInjection->getParameters();
        $this->assertCount(1, $parameters);

        // Offset is 1, not 0, because parameter 0 wasn't defined
        $this->assertEquals(new EntryReference('bar'), $parameters[1]);
    }

    public function testUnannotatedMethod()
    {
        $source = new AnnotationReader();
        $definition = $source->getDefinition('DI\Test\UnitTest\Definition\Source\Fixtures\AnnotationFixture');

        $this->assertNull($this->getMethodInjection($definition, 'unannotatedMethod'));
    }

    /**
     * @test
     */
    public function optionalParametersShouldBeIgnored()
    {
        $source = new AnnotationReader();
        $definition = $source->getDefinition('DI\Test\UnitTest\Definition\Source\Fixtures\AnnotationFixture');

        $methodInjection = $this->getMethodInjection($definition, 'optionalParameter');

        $parameters = $methodInjection->getParameters();
        $this->assertCount(1, $parameters);

        $this->assertEquals(new EntryReference('foo'), $parameters[0]);
        $this->assertArrayNotHasKey(1, $parameters);
    }

    public function testStaticMethod()
    {
        $source = new AnnotationReader();
        $definition = $source->getDefinition('DI\Test\UnitTest\Definition\Source\Fixtures\AnnotationFixture');

        $this->assertNull($this->getMethodInjection($definition, 'staticMethod'));
    }

    public function testInjectable()
    {
        $source = new AnnotationReader();
        $definition = $source->getDefinition('DI\Test\UnitTest\Definition\Source\Fixtures\AnnotationInjectableFixture');
        $this->assertInstanceOf('DI\Definition\Definition', $definition);

        $this->assertEquals(Scope::PROTOTYPE, $definition->getScope());
        $this->assertTrue($definition->isLazy());
    }

    /**
     * @see https://github.com/mnapoli/PHP-DI/issues/99
     */
    public function testIssue99()
    {
        $source = new AnnotationReader();
        $definition = $source->getDefinition('DI\Test\UnitTest\Definition\Source\Fixtures\AnnotationFixture3');
        $this->assertInstanceOf('DI\Definition\Definition', $definition);

        $methodInjection = $this->getMethodInjection($definition, 'method1');
        $this->assertInstanceOf('DI\Definition\ObjectDefinition\MethodInjection', $methodInjection);

        $parameters = $methodInjection->getParameters();
        $this->assertCount(1, $parameters);
        $this->assertEquals(
            new EntryReference('DI\Test\UnitTest\Definition\Source\Fixtures\AnnotationFixture2'),
            $parameters[0]
        );
    }

    /**
     * @see https://github.com/mnapoli/PHP-DI/issues/184
     * @expectedException \PhpDocReader\AnnotationException
     */
    public function testFailWithPhpDocErrors()
    {
        $source = new AnnotationReader();
        $source->getDefinition('DI\Test\UnitTest\Definition\Source\Fixtures\AnnotationFixture5');
    }

    /**
     * @see https://github.com/mnapoli/PHP-DI/issues/184
     */
    public function testIgnorePhpDocErrors()
    {
        $source = new AnnotationReader($ignorePhpDocErrors = true);
        $source->getDefinition('DI\Test\UnitTest\Definition\Source\Fixtures\AnnotationFixture5');
    }

    public function testMergedWithParentDefinition()
    {
        $source = new AnnotationReader();
        $definition = $source->getDefinition('DI\Test\UnitTest\Definition\Source\Fixtures\AnnotationFixtureChild');

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
        $source = new AnnotationReader();
        $definition = $source->getDefinition('DI\Test\UnitTest\Definition\Source\Fixtures\AnnotationFixtureChild');
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
