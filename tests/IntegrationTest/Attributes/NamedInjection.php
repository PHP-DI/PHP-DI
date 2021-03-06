<?php

declare(strict_types=1);

namespace DI\Test\IntegrationTest\Attributes;

use DI\Attribute\Inject;

class NamedInjection
{
    #[Inject('namedDependency')]
    public $dependency1;

    #[Inject(name: 'namedDependency')]
    public $dependency2;
}
