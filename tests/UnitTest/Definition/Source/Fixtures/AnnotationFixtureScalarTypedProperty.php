<?php

declare(strict_types=1);

namespace DI\Test\UnitTest\Definition\Source\Fixtures;

class AnnotationFixtureScalarTypedProperty
{
    /**
     * @Inject
     */
    protected int $scalarTypeAndInject;
}
