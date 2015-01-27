<?php

return array(
    'defined-env'         => DI\env('USER'),
    'undefined-env'       => DI\env('PHP_DI_DO_NOT_DEFINE_THIS'),
    'optional-env'        => DI\env('PHP_DI_DO_NOT_DEFINE_THIS', '<default>'),
    'optional-env-null'   => DI\env('PHP_DI_DO_NOT_DEFINE_THIS', null),
    'optional-env-linked' => DI\env('PHP_DI_DO_NOT_DEFINE_THIS', DI\link('foo')),
    'foo'                 => 'bar',
);
