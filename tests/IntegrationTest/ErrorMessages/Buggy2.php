<?php

declare(strict_types=1);

namespace DI\Test\IntegrationTest\ErrorMessages;

use DI\Attribute\Inject;

class Buggy2
{
    #[Inject(['nonExistentEntry'])]
    public function __construct($dependency)
    {
    }
}
