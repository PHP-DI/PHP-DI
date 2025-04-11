<?php

declare(strict_types=1);

namespace DI\Test\UnitTest\Definition;

use DI\Definition\Reference;
use EasyMock\EasyMock;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

/**
 * @covers \DI\Definition\Reference
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\DI\Definition\Reference::class)]
class ReferenceTest extends TestCase
{
    use EasyMock;

    /**
     * @test
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function should_have_a_name()
    {
        $definition = new Reference('bar');
        $this->assertEquals('', $definition->getName());
        $definition->setName('foo');
        $this->assertEquals('foo', $definition->getName());
    }

    /**
     * @test
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function should_have_a_target_entry_name()
    {
        $definition = new Reference('bar');

        $this->assertEquals('bar', $definition->getTargetEntryName());
    }

    /**
     * @test
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function should_resolve()
    {
        $container = $this->easySpy(ContainerInterface::class, [
            'get' => 42,
        ]);

        $definition = new Reference('bar');

        $this->assertEquals(42, $definition->resolve($container));
    }

    /**
     * @test
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function should_be_resolvable()
    {
        $container = $this->easySpy(ContainerInterface::class, [
            'has' => true,
        ]);

        $definition = new Reference('foo');

        $this->assertTrue($definition->isResolvable($container));
    }

    /**
     * @test
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function should_cast_to_string()
    {
        $this->assertEquals('get(bar)', (string) new Reference('bar'));
    }
}
