<?php

declare(strict_types=1);

namespace DI\Test\IntegrationTest\ErrorMessages;

use DI\Annotation\Inject;

class Buggy4
{
    /**
     * @Inject({"nonExistentBean"})
     */
    public function setDependency($dependency)
    {
    }
}
