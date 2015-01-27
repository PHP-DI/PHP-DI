<?php

return array(

    // Extends a definition of `array-definitions.php`
    'values' => DI\add(array(
        'another value',
        DI\link('foo'),
    )),

    'foo' => DI\object('stdClass'),

);
