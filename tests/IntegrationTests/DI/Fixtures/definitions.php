<?php

use DI\Scope;

return array(
    'foo'                                          => 'bar',
    'IntegrationTests\DI\Fixtures\Class1'          => array(
        'scope'       => Scope::PROTOTYPE(),
        'properties'  => array(
            'property1' => 'IntegrationTests\DI\Fixtures\Class2',
            'property2' => 'IntegrationTests\DI\Fixtures\Interface1',
            'property3' => 'namedDependency',
            'property4' => 'foo',
            'property5' => array(
                'name' => 'IntegrationTests\DI\Fixtures\LazyDependency',
                'lazy' => true,
            ),
            'property6' => 'simpleValueDependency',
        ),
        'constructor' => array(
            'param1' => 'IntegrationTests\DI\Fixtures\Class2',
            'param2' => 'IntegrationTests\DI\Fixtures\Interface1',
            'param3' => array(
                'name' => 'IntegrationTests\DI\Fixtures\LazyDependency',
                'lazy' => true,
            ),
            'simpleValueDependency' => 'simpleValueDependency',
        ),
        'methods'     => array(
            'method1' => 'IntegrationTests\DI\Fixtures\Class2',
            'method2' => array('IntegrationTests\DI\Fixtures\Interface1'),
            'method3' => array(
                'param1' => 'namedDependency',
                'param2' => 'foo',
            ),
            'method4' => array(
                'param1' => array(
                    'name' => 'IntegrationTests\DI\Fixtures\LazyDependency',
                    'lazy' => true,
                ),
            ),
            'method5' => array(
                'param1' => 'simpleValueDependency',
            ),
        ),
    ),
    'IntegrationTests\DI\Fixtures\Class2'          => array(),
    'IntegrationTests\DI\Fixtures\Implementation1' => array(),
    'IntegrationTests\DI\Fixtures\Interface1'      => array(
        'class' => 'IntegrationTests\DI\Fixtures\Implementation1',
        'scope' => 'singleton',
    ),
    'namedDependency'                              => array(
        'class' => 'IntegrationTests\DI\Fixtures\Class2',
    ),
    'IntegrationTests\DI\Fixtures\LazyDependency' => array(),
);
