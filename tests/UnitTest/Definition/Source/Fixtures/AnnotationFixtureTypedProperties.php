<?php

declare(strict_types=1);

namespace DI\Test\UnitTest\Definition\Source\Fixtures;

use DI\Annotation\Inject;

class AnnotationFixtureTypedProperties
{
    protected AnnotationFixture2 $typedButNoInject;

    /**
     * @Inject
     */
    protected AnnotationFixture2 $typedAndInject;

    /**
     * @Inject
     * @var AnnotationFixture3
     */
    protected AnnotationFixture2 $typedAndVar;

    /**
     * @Inject("name")
     */
    protected AnnotationFixture2 $typedAndNamed;
}
