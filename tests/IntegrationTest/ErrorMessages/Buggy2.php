<?php

declare(strict_types=1);

namespace DI\Test\IntegrationTest\ErrorMessages;

use DI\Annotation\Inject;

class Buggy2
{
    /**
     * @Inject({"nonExistentEntry"})
     * @param $dependency
     */
    public function __construct($dependency)
    {
    }
}
