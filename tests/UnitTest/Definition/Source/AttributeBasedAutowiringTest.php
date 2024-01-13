<?php

declare(strict_types=1);

namespace DI\Test\UnitTest\Definition\Source;

use DI\Definition\Definition;
use DI\Definition\Exception\InvalidAttribute;
use DI\Definition\ObjectDefinition;
use DI\Definition\ObjectDefinition\MethodInjection;
use DI\Definition\ObjectDefinition\PropertyInjection;
use DI\Definition\Reference;
use DI\Definition\Source\AttributeBasedAutowiring;
use DI\Definition\Source\ReflectionBasedAutowiring;
use DI\Test\UnitTest\Definition\Source\Fixtures\AnnotationFixture2;
use DI\Test\UnitTest\Definition\Source\Fixtures\AnnotationFixture3;
use DI\Test\UnitTest\Definition\Source\Fixtures\AnnotationFixture4;
use DI\Test\UnitTest\Definition\Source\Fixtures\AnnotationFixture5;
use DI\Test\UnitTest\Definition\Source\Fixtures\AnnotationFixtureChild;
use DI\Test\UnitTest\Definition\Source\Fixtures\AnnotationFixtureScalarTypedProperty;
use DI\Test\UnitTest\Definition\Source\Fixtures\AnnotationFixtureTypedProperties;
use DI\Test\UnitTest\Definition\Source\Fixtures\AnnotationInjectableFixture;
use DI\Test\UnitTest\Definition\Source\Fixtures\AttributeFixture;
use DI\Test\UnitTest\Definition\Source\Fixtures\AttributeFixturePromotedProperty;
use DI\Test\UnitTest\Definition\Source\Fixtures\AutowiringFixture;
use Generator;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

/**
 * @covers \DI\Definition\Source\AttributeBasedAutowiring
 */
class AttributeBasedAutowiringTest extends TestCase
{
    public function testUnknownClass()
    {
        $this->assertNull((new AttributeBasedAutowiring)->autowire('foo'));
    }

    public function testProperty1()
    {
        $definition = (new AttributeBasedAutowiring)->autowire(AttributeFixture::class);
        $this->assertInstanceOf(Definition::class, $definition);

        $properties = $definition->getPropertyInjections();
        $this->assertInstanceOf(PropertyInjection::class, $properties['property1']);

        $property = $properties['property1'];
        $this->assertSame('property1', $property->getPropertyName());
        $this->assertEquals(new Reference('foo'), $property->getValue());
    }

    public function testUnannotatedProperty()
    {
        $definition = (new AttributeBasedAutowiring)->autowire(AttributeFixture::class);

        $this->assertNotHasPropertyInjection($definition, 'unannotatedProperty');
    }

    public function testStaticProperty()
    {
        $definition = (new AttributeBasedAutowiring)->autowire(AttributeFixture::class);

        $this->assertNotHasPropertyInjection($definition, 'staticProperty');
    }

    public function testUnguessableProperty()
    {
        $this->expectException(InvalidAttribute::class);
        $this->expectExceptionMessage('#[Inject] found on property DI\Test\UnitTest\Definition\Source\Fixtures\AnnotationFixture4::property but unable to guess what to inject, please add a type to the property');
        (new AttributeBasedAutowiring)->autowire(AnnotationFixture4::class);
    }

    public function testTypedProperty()
    {
        $definition = (new AttributeBasedAutowiring)->autowire(AnnotationFixtureTypedProperties::class);

        $this->assertNotHasPropertyInjection($definition, 'typeAndNoInject');
        $this->assertHasPropertyInjection($definition, 'typedAndInject', AnnotationFixture2::class);
        $this->assertHasPropertyInjection($definition, 'typedAndNamed', 'name');
    }

    public function testScalarTypedPropertiesFail()
    {
        $this->expectException(InvalidAttribute::class);
        (new AttributeBasedAutowiring)->autowire(AnnotationFixtureScalarTypedProperty::class);
    }

    public function testConstructor()
    {
        $definition = (new AttributeBasedAutowiring)->autowire(AttributeFixture::class);
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
        $definition = (new AttributeBasedAutowiring)->autowire(AttributeFixture::class);
        $this->assertInstanceOf(Definition::class, $definition);

        $methodInjection = $this->getMethodInjection($definition, 'method1');
        $this->assertInstanceOf(MethodInjection::class, $methodInjection);

        $this->assertEmpty($methodInjection->getParameters());
    }

    public function testMethod2()
    {
        $definition = (new AttributeBasedAutowiring)->autowire(AttributeFixture::class);
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
        $definition = (new AttributeBasedAutowiring)->autowire(AttributeFixture::class);
        $this->assertInstanceOf(Definition::class, $definition);

        $methodInjection = $this->getMethodInjection($definition, 'method3');
        $this->assertInstanceOf(MethodInjection::class, $methodInjection);

        $parameters = $methodInjection->getParameters();
        $this->assertCount(1, $parameters);

        $reference = new Reference(AnnotationFixture2::class);
        $this->assertEquals($reference, $parameters[0]);
    }

    public function testMethod4()
    {
        $definition = (new AttributeBasedAutowiring)->autowire(AttributeFixture::class);
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
        $definition = (new AttributeBasedAutowiring)->autowire(AttributeFixture::class);
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
        $definition = (new AttributeBasedAutowiring)->autowire(AttributeFixture::class);

        $this->assertNull($this->getMethodInjection($definition, 'unannotatedMethod'));
    }

    public function testOptionalParametersShouldBeIgnored()
    {
        $definition = (new AttributeBasedAutowiring)->autowire(AttributeFixture::class);

        $methodInjection = $this->getMethodInjection($definition, 'optionalParameter');

        $parameters = $methodInjection->getParameters();
        $this->assertCount(1, $parameters);

        $this->assertEquals(new Reference('foo'), $parameters[0]);
        $this->assertArrayNotHasKey(1, $parameters);
    }

    public function testStaticMethod()
    {
        $definition = (new AttributeBasedAutowiring)->autowire(AttributeFixture::class);

        $this->assertNull($this->getMethodInjection($definition, 'staticMethod'));
    }

    public function testInjectable()
    {
        $definition = (new AttributeBasedAutowiring)->autowire(AnnotationInjectableFixture::class);
        $this->assertInstanceOf(Definition::class, $definition);
        $this->assertTrue($definition->isLazy());
    }

    public function testMethodInjectionWithPrimitiveTypeCausesAnError()
    {
        $definition = (new AttributeBasedAutowiring)->autowire(AnnotationFixture3::class);
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

    public function testFailWithTypeError()
    {
        $this->expectException(InvalidAttribute::class);
        $this->expectExceptionMessage('#[Inject] found on property DI\Test\UnitTest\Definition\Source\Fixtures\AnnotationFixture5::property but unable to guess what to inject, the type of the property does not look like a valid class or interface name');
        (new AttributeBasedAutowiring)->autowire(AnnotationFixture5::class);
    }

    public function testMergedWithParentDefinition()
    {
        $definition = (new AttributeBasedAutowiring)->autowire(AnnotationFixtureChild::class);

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
        $definition = (new AttributeBasedAutowiring)->autowire(AnnotationFixtureChild::class);
        $this->assertHasPropertyInjection($definition, 'propertyParentPrivate');
    }

    public function testPromotedProperties(): void
    {
        $definition = (new AttributeBasedAutowiring)->autowire(AttributeFixturePromotedProperty::class);
        $this->assertNotHasPropertyInjection($definition, 'promotedProperty');

        $constructorInjection = $definition->getConstructorInjection();
        $this->assertInstanceOf(MethodInjection::class, $constructorInjection);

        $parameters = $constructorInjection->getParameters();
        $this->assertCount(1, $parameters);
        $this->assertEquals(new Reference('foo'), $parameters[0]);
    }


    public function testAutowireHitLoggedAtDefaultLogLevel(): void
    {
        $class = AutowiringFixture::class;
        $loggerMock = $this->createMock(LoggerInterface::class);
        $loggerMock->expects($this->once())
            ->method('log')
            // Log level is set to debug by default
            ->with(LogLevel::DEBUG, "Autowiring {$class}");

        $autowiring = (new ReflectionBasedAutowiring())->setLogger($loggerMock);

        $autowiring->autowire($class);
    }

    /** @dataProvider availableLogLevels */
    public function testAutowireHitLogged(string $logLevel): void
    {
        $class = AutowiringFixture::class;
        $loggerMock = $this->createMock(LoggerInterface::class);
        $loggerMock->expects($this->once())
            ->method('log')
            ->with($logLevel, "Autowiring {$class}");

        $autowiring = (new ReflectionBasedAutowiring())->setLogger($loggerMock, $logLevel);

        $autowiring->autowire($class);
    }

    /** @return Generator<string> */
    public function availableLogLevels(): Generator
    {
        yield 'debug' => [LogLevel::DEBUG];
        yield 'info' => [LogLevel::INFO];
        yield 'notice' => [LogLevel::NOTICE];
        yield 'warning' => [LogLevel::WARNING];
        yield 'error' => [LogLevel::ERROR];
        yield 'critical' => [LogLevel::CRITICAL];
        yield 'alert' => [LogLevel::ALERT];
        yield 'emergency' => [LogLevel::EMERGENCY];
    }

    private function getMethodInjection(ObjectDefinition $definition, $name) : ?MethodInjection
    {
        $methodInjections = $definition->getMethodInjections();
        foreach ($methodInjections as $methodInjection) {
            if ($methodInjection->getMethodName() === $name) {
                return $methodInjection;
            }
        }

        return null;
    }

    private function assertHasPropertyInjection(ObjectDefinition $definition, $propertyName, ?string $expectedType = null)
    {
        $propertyInjections = $definition->getPropertyInjections();
        foreach ($propertyInjections as $propertyInjection) {
            if ($propertyInjection->getPropertyName() === $propertyName) {
                if ($expectedType !== null) {
                    $this->assertInstanceOf(Reference::class, $propertyInjection->getValue());
                    $this->assertSame(
                        $expectedType,
                        $propertyInjection->getValue()->getTargetEntryName(),
                        'Property injected with the right type'
                    );
                }

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
                $this->fail('Unexpected property injection found for ' . $propertyName);
            }
        }
    }
}
