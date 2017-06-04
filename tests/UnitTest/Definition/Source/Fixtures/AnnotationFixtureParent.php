<?php

declare(strict_types=1);

namespace DI\Test\UnitTest\Definition\Source\Fixtures;

use DI\Annotation\Inject;

/**
 * Used to check that child classes also have the injections of the parent classes.
 */
class AnnotationFixtureParent
{
    /**
     * @Inject("foo")
     */
    protected $propertyParent;

    /**
     * @Inject("foo")
     */
    private $propertyParentPrivate;

    /**
     * @Inject
     */
    public function methodParent()
    {
    }
}
