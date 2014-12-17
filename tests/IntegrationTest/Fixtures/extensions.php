<?php

return array(

    // Extends a definition of `definitions.php`
    'array' => DI\add(array(
        'another value',
        DI\link('DI\Test\IntegrationTest\Fixtures\Interface1'),
    )),

);
