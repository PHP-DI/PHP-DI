<?php

declare(strict_types=1);

namespace DI\Test\UnitTest\Attributes;

use DI\Attribute\Injectable;
use DI\Test\UnitTest\Attributes\Fixtures\Injectable1;
use DI\Test\UnitTest\Attributes\Fixtures\Injectable2;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Injectable attribute test class.
 *
 * @requires PHP >= 8
 *
 * @covers \DI\Attribute\Injectable
 */
class InjectableTest extends TestCase
{
    public function testEmptyAnnotation()
    {
        $class = new ReflectionClass(Injectable1::class);
        /** @var Injectable $annotation */
        $annotation = $class->getAttributes(Injectable::class)[0]->newInstance();

        $this->assertInstanceOf(Injectable::class, $annotation);
        $this->assertNull($annotation->isLazy());
    }

    public function testLazy()
    {
        $class = new ReflectionClass(Injectable2::class);
        /** @var Injectable $annotation */
        $annotation = $class->getAttributes(Injectable::class)[0]->newInstance();

        $this->assertInstanceOf(Injectable::class, $annotation);
        $this->assertTrue($annotation->isLazy());
    }
}
