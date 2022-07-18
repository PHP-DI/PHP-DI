<?php

declare(strict_types=1);

namespace DI\Test\UnitTest\Definition\Source\Fixtures;

use DI\Attribute\Inject;

/**
 * Used to check that child classes also have the injections of the parent classes.
 */
class AnnotationFixtureChild extends AnnotationFixtureParent
{
    #[Inject('foo')]
    protected $propertyChild;

    #[Inject]
    public function methodChild()
    {
    }
}
