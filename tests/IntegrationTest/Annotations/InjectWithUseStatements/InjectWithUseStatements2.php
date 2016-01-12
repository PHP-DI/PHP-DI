<?php

namespace DI\Test\IntegrationTest\Annotations\InjectWithUseStatements;

use DI\Annotation\Inject;
use DI\Test\IntegrationTest\Annotations\InjectWithUseStatements;

class InjectWithUseStatements2
{
    /**
     * @Inject
     * @var InjectWithUseStatements
     */
    public $dependency;
}
