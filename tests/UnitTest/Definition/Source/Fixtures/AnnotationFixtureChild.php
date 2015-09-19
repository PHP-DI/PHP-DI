<?php

namespace DI\Test\UnitTest\Definition\Source\Fixtures;

use DI\Annotation\Inject;

/**
 * Used to check that child classes also have the injections of the parent classes.
 */
class AnnotationFixtureChild extends AnnotationFixtureParent
{
    /**
     * @Inject("foo")
     */
    protected $propertyChild;

    /**
     * @Inject
     */
    public function methodChild()
    {
    }
}
