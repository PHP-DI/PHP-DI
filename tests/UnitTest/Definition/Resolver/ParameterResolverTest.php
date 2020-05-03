<?php
declare(strict_types=1);

namespace DI\Test\UnitTest\Definition\Resolver;

use DI\Definition\Definition;
use DI\Definition\Exception\InvalidDefinition;
use DI\Definition\ObjectDefinition\MethodInjection;
use DI\Definition\Reference;
use DI\Definition\Resolver\DefinitionResolver;
use DI\Definition\Resolver\ParameterResolver;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use function DI\get;

class ParameterResolverTest extends TestCase
{
    /**
     * @var ParameterResolver
     */
    private $sut;

    /**
     * @var MockObject
     */
    private $definition;

    /**
     * @var MockObject
     */
    private $definitionMock;

    /**
     * @var MockObject
     */
    private $reflectionMethodMock;

    function setUp(): void
    {
        $this->definition = $this->createMock(Resolver::class);
        $this->sut = new ParameterResolver($this->definition);
        $this->definitionMock = $this->createMock(MethodInjection::class);
        $this->reflectionMethodMock = $this->createMock(\ReflectionMethod::class);
    }

    function testShouldReturnParametersWhenMethodIsNull()
    {
        $this->assertEquals([], $this->sut->resolveParameters());
    }

    function testShouldReturnEmptyArrayAndNeverCallDefinitionWhenMethodHasNoParameters()
    {
        $mock = $this->createMock(MethodInjection::class);
        $mock->expects($this->never())
            ->method('getParameters');
        $args = $this->sut->resolveParameters($mock, null, $expected = []);
        $this->assertSame([], $args);
    }

    function testShouldReturnEmptyArrayWhenParameterIsSetButMethodDoesNotAcceptAny()
    {
        $this->expectOnceAndReturn($this->definitionMock, ['foo']);
        $this->expectOnceAndReturn($this->reflectionMethodMock, []);
        $args = $this->sut->resolveParameters($this->definitionMock, $this->reflectionMethodMock, $expected = []);
        $this->assertSame([], $args);
    }

    function testShouldReturnProvidedParameterInArrayWhenParameterIsSetForMethod()
    {
        $this->expectOnceAndReturn($this->definitionMock, ['foo']);
        $this->expectOnceAndReturn($this->reflectionMethodMock, [new \ReflectionParameter(function ($foo) {}, 'foo')]);
        $args = $this->sut->resolveParameters($this->definitionMock, $this->reflectionMethodMock, $expected = []);
        $this->assertEquals(['foo'], $args);
    }

    function testShouldReturnProvidedValueInArrayWhenParameterIsSetForMethod()
    {
        $provided = ['foo' => 'hello'];
        $this->expectOnceAndReturn($this->definitionMock, ['foo' => 'hello']);
        $this->expectOnceAndReturn($this->reflectionMethodMock, [new \ReflectionParameter(function ($foo) {}, 'foo')]);
        $args = $this->sut->resolveParameters($this->definitionMock, $this->reflectionMethodMock, $provided);
        $this->assertEquals(['hello'], $args);
    }

    function testShouldUseValueFromMethodInjectionInArrayWhenParameterIsSetForMethod()
    {
        $this->expectOnceAndReturn($this->definitionMock, ['hello']);
        $this->expectOnceAndReturn($this->reflectionMethodMock, [new \ReflectionParameter(function ($foo) {}, 'foo')]);
        $args = $this->sut->resolveParameters($this->definitionMock, $this->reflectionMethodMock);
        $this->assertEquals(['hello'], $args);
    }

    function testShouldSetDefaultValueWhenParameterSupportsIt()
    {
        $this->expectOnceAndReturn($this->definitionMock, []);
        $this->expectOnceAndReturn($this->reflectionMethodMock, [new \ReflectionParameter(function ($param = 1) {}, 'param')]);
        $args = $this->sut->resolveParameters($this->definitionMock, $this->reflectionMethodMock);
        $this->assertEquals([1], $args);
    }

    function testShouldOverrideDefaultFromProvidedValuesValueWhenParameterSupportsIt()
    {
        $this->expectOnceAndReturn($this->definitionMock, []);
        $this->expectOnceAndReturn($this->reflectionMethodMock, [new \ReflectionParameter(function ($param = 1) {}, 'param')]);
        $args = $this->sut->resolveParameters($this->definitionMock, $this->reflectionMethodMock, ['param' => 34]);
        $this->assertEquals([34], $args);
    }

    function testShouldOverrideDefaultFromInjectionValueWhenParameterSupportsIt()
    {
        $this->expectOnceAndReturn($this->definitionMock, [2]);
        $this->expectOnceAndReturn($this->reflectionMethodMock, [new \ReflectionParameter(function ($param = 1) {}, 'param')]);
        $args = $this->sut->resolveParameters($this->definitionMock, $this->reflectionMethodMock);
        $this->assertEquals([2], $args);
    }

    function testShouldSetDefaultValueWhenComplexParameterSupportsIt()
    {
        $this->expectOnceAndReturn($this->definitionMock, []);
        $this->expectOnceAndReturn($this->reflectionMethodMock, [new \ReflectionParameter(function (Resolver $param = null) {}, 'param')]);
        $args = $this->sut->resolveParameters($this->definitionMock, $this->reflectionMethodMock);
        $this->assertEquals([null], $args);
    }

    function testShouldNotOverrideDefaultFromBadProvidedValuesWhenComplexParameterSupportsIt()
    {
        $this->expectOnceAndReturn($this->definitionMock, []);
        $this->expectOnceAndReturn($this->reflectionMethodMock, [new \ReflectionParameter(function (Resolver $param = null) {}, 'param')]);
        $args = $this->sut->resolveParameters($this->definitionMock, $this->reflectionMethodMock, ['param1' => 34]);
        $this->assertEquals([null], $args);
    }

    function testShouldOverrideDefaultFromInjectionValueWhenComplexParameterSupportsIt()
    {
        $this->expectOnceAndReturn($this->definitionMock, [new \stdClass]);
        $this->expectOnceAndReturn($this->reflectionMethodMock, [new \ReflectionParameter(function (Resolver $param = null) {}, 'param')]);
        $args = $this->sut->resolveParameters($this->definitionMock, $this->reflectionMethodMock);
        $this->assertEquals([new \stdClass()], $args);
    }

    function testShouldSetDefaultValueForOptionalParameter()
    {
        $callable = function ($param, $optional = 1) {};
        $this->expectOnceAndReturn($this->definitionMock, [null]);
        $this->expectOnceAndReturn($this->reflectionMethodMock, [
            new \ReflectionParameter($callable, 'param'),
            new \ReflectionParameter($callable, 'optional')
        ]);
        $args = $this->sut->resolveParameters($this->definitionMock, $this->reflectionMethodMock);
        $this->assertEquals([null, 1], $args);
    }

    function testShouldSetDefaultValueForTypedOptionalParameter()
    {
        $callable = function ($param, string $optional = '') {};
        $this->expectOnceAndReturn($this->definitionMock, [null]);
        $this->expectOnceAndReturn($this->reflectionMethodMock, [
            new \ReflectionParameter($callable, 'param'),
            new \ReflectionParameter($callable, 'optional')
        ]);
        $args = $this->sut->resolveParameters($this->definitionMock, $this->reflectionMethodMock);
        $this->assertEquals([null, ''], $args);
    }

    function testShouldOverrideDefaultValueForOptionalParameter()
    {
        $callable = function ($param, $optional = 1) {};
        $this->expectOnceAndReturn($this->definitionMock, [null, 4]);
        $this->expectOnceAndReturn($this->reflectionMethodMock, [
            new \ReflectionParameter($callable, 'param'),
            new \ReflectionParameter($callable, 'optional')
        ]);
        $args = $this->sut->resolveParameters($this->definitionMock, $this->reflectionMethodMock);
        $this->assertEquals([null, 4], $args);
    }

    function testShouldSetDefaultValueForOptionalComplexParameter()
    {
        $callable = function ($param, Resolver $optional = null) {};
        $this->expectOnceAndReturn($this->definitionMock, ['test']);
        $this->expectOnceAndReturn($this->reflectionMethodMock, [
            new \ReflectionParameter($callable, 'param'),
            new \ReflectionParameter($callable, 'optional')
        ]);
        $args = $this->sut->resolveParameters($this->definitionMock, $this->reflectionMethodMock);
        $this->assertEquals(['test', null], $args);
    }

    function testShouldOverrideDefaultValueForOptionalComplexParameter()
    {
        $obj = new Resolver;
        $callable = function ($param, Resolver $optional = null) {};
        $this->expectOnceAndReturn($this->definitionMock, ['test', $obj]);
        $this->expectOnceAndReturn($this->reflectionMethodMock, [
            new \ReflectionParameter($callable, 'param'),
            new \ReflectionParameter($callable, 'optional')
        ]);
        $args = $this->sut->resolveParameters($this->definitionMock, $this->reflectionMethodMock);
        $this->assertEquals(['test', $obj], $args);
    }

    function testShouldOverrideDefaultValueFromResolverForOptionalTypedParameter()
    {
        $reference = get(Resolver::class);
        $ret = new Resolver;
        $this->definition
            ->expects($this->once())
            ->method('isResolvable')
            ->with($reference)
            ->willReturn(true);
        $this->definition
            ->expects($this->once())
            ->method('resolve')
            ->with($reference)
            ->willReturn($ret);
        $callable = function ($param, Resolver $optional = null) {};
        $this->expectOnceAndReturn($this->definitionMock, [null]);
        $this->expectOnceAndReturn($this->reflectionMethodMock, [
            new \ReflectionParameter($callable, 'param'),
            new \ReflectionParameter($callable, 'optional')
        ]);
        $args = $this->sut->resolveParameters($this->definitionMock, $this->reflectionMethodMock);
        $this->assertEquals([null, $ret], $args);
    }

    function testShouldOverrideDefaultValueFromResolverForOptionalComplexParameter()
    {
        $this->definition
            ->expects($this->never())
            ->method('isResolvable');
        $this->definition
            ->expects($this->never())
            ->method('resolve');
        $callable = function ($param, string $optional = '') {};
        $this->expectOnceAndReturn($this->definitionMock, [null, 'foo']);
        $this->expectOnceAndReturn($this->reflectionMethodMock, [
            new \ReflectionParameter($callable, 'param'),
            new \ReflectionParameter($callable, 'optional')
        ]);
        $args = $this->sut->resolveParameters($this->definitionMock, $this->reflectionMethodMock);
        $this->assertEquals([null, 'foo'], $args);
    }

    private function expectOnceAndReturn(MockObject $mock, $return)
    {
        $mock->expects($this->once())
            ->method('getParameters')
            ->willReturn($return);
    }
}

class Resolver implements DefinitionResolver
{
    public $instance;
    public function resolve(Definition $definition, array $parameters = [])
    {
        return $this->instance;
    }

    public function isResolvable(Definition $definition, array $parameters = []): bool
    {
        return true;
    }
}
