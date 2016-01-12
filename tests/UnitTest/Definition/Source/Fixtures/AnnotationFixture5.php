<?php

namespace DI\Test\UnitTest\Definition\Source\Fixtures;

class AnnotationFixture5
{
    /**
     * @Inject
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
