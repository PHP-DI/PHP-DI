<?php

declare(strict_types=1);

namespace DI\Test\UnitTest\Definition\Source\Fixtures;

class AnnotationFixture5
{
    /**
     * @var foobar
     */
    public $property;

    /**
     * @param foobar $foo
     */
    public function __construct($foo)
    {
    }
}
