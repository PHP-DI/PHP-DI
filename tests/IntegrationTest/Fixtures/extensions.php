<?php

return array(

    // Extends a definition of `definitions.php` with the same name
    'array' => DI\extend()->add(array(
        'another value',
        DI\link('DI\Test\IntegrationTest\Fixtures\Interface1'),
    )),

    // Extends a definition with a different name
    'extend-array' => DI\extend('array2')->add(array(
        'a second value',
    )),

    // Extends a definition of the same file with a different name
    'extend-array-same-file' => DI\extend('array')->add(array(
        'a third value',
    )),

);
