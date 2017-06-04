<?php

declare(strict_types=1);

namespace DI\Test\IntegrationTest\Annotations;

use DI\Annotation\Inject;

class NamedInjection
{
    /**
     * @Inject(name="namedDependency")
     */
    public $dependency;
}
