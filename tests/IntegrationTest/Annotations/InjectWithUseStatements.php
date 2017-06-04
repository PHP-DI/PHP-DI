<?php

declare(strict_types=1);

namespace DI\Test\IntegrationTest\Annotations;

use DI\Annotation\Inject;
use DI\Test\IntegrationTest\Annotations\A as Alias;
use DI\Test\IntegrationTest\Annotations as NamespaceAlias;

class InjectWithUseStatements
{
    /**
     * @Inject
     * @var A
     */
    public $a;

    /**
     * @Inject
     * @var Alias
     */
    public $alias;

    /**
     * @Inject
     * @var NamespaceAlias\A
     */
    public $namespaceAlias;
}
