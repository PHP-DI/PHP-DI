<?php

use DI\Scope;

return array(
    'value1' => 'abc',
    'value2' => 123,
    'value3' => true,
    'namespace\class1' => array(
        'scope' => Scope::SINGLETON(),
        'lazy' => true,
        'constructor' => array(
            'name1' => 'argument1',
            'name2' => 'argument2',
            'name3' => 'argument3'
        ),
        'methods' => array(
            'method1' => array(
                'name4' => 'argument4',
                'name5' => 'argument5'
            ),
            'method2' => array(
                'name6' => 'argument6'
            ),
            'method3' => array('argument7', 'argument8')
        ),
        'properties' => array(
            'property1' => 'value1',
            'property2' => array(
                'name' => 'interface1',
                'lazy' => true
            )
        )
    ),
    'interface1' => array(
        'class' => 'namespace\\class1',
        'lazy' => false
    ),
    'interface2' => array(
        'class' => 'class2',
        'scope' => Scope::PROTOTYPE()
    )
);
