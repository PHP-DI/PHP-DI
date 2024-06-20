<?php

declare(strict_types=1);

namespace DI\Test\UnitTest\Definition\Source;

use DI\Definition\Reference;
use Generator;
use PHPUnit\Framework\TestCase;
use DI\Definition\ObjectDefinition;
use DI\Definition\ObjectDefinition\MethodInjection;
use DI\Definition\Source\ReflectionBasedAutowiring;
use DI\Test\UnitTest\Definition\Source\Fixtures\AutowiringFixture;
use DI\Test\UnitTest\Definition\Source\Fixtures\AutowiringFixtureChild;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

/**
 * @covers \DI\Definition\Source\ReflectionBasedAutowiring
 */
class ReflectionBasedAutowiringTest extends TestCase
{
    public function testUnknownClass()
    {
        $source = new ReflectionBasedAutowiring();
        $this->assertNull($source->autowire('foo'));
    }

    public function testConstructor()
    {
        $definition = (new ReflectionBasedAutowiring)->autowire(AutowiringFixture::class);
        $this->assertInstanceOf(ObjectDefinition::class, $definition);

        $constructorInjection = $definition->getConstructorInjection();
        $this->assertInstanceOf(MethodInjection::class, $constructorInjection);

        $parameters = $constructorInjection->getParameters();
        $this->assertCount(1, $parameters);

        $param1 = $parameters[0];
        $this->assertEquals(new Reference(AutowiringFixture::class), $param1);
    }

    public function testConstructorInParentClass()
    {
        $definition = (new ReflectionBasedAutowiring)->autowire(AutowiringFixtureChild::class);
        $this->assertInstanceOf(ObjectDefinition::class, $definition);

        $constructorInjection = $definition->getConstructorInjection();
        $this->assertInstanceOf(MethodInjection::class, $constructorInjection);

        $parameters = $constructorInjection->getParameters();
        $this->assertCount(1, $parameters);

        $param1 = $parameters[0];
        $this->assertEquals(new Reference(AutowiringFixture::class), $param1);
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
}
