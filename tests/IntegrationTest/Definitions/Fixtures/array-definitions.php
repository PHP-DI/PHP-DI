<?php

use DI\Scope;

return array(

    'values'    => array(
        'value 1',
        'value 2',
    ),
    'links'     => array(
        DI\link('singleton'),
        DI\link('prototype'),
    ),

    'singleton' => DI\object('stdClass'),
    'prototype' => DI\object('stdClass')
        ->scope(Scope::PROTOTYPE()),

);
