<?php

declare(strict_types=1);

namespace DI\Test\IntegrationTest\Annotations;

use DI\Annotation\Inject;

class NotFoundVarAnnotation
{
    /**
     * @Inject
     * @var this_is_a_non_existent_class
     */
    public $class2;
}
