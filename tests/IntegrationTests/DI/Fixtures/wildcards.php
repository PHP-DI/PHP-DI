<?php

return array(
    'foo*' => 'bar',

    'IntegrationTests\DI\*\Interface*' => DI\object('IntegrationTests\DI\*\Implementation*'),
);
