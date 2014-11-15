<?php

return array(
    'foo*' => 'bar',

    'DI\Test\IntegrationTest\*\Interface*' => DI\object('DI\Test\IntegrationTest\*\Implementation*'),
);
