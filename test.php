<?php

use DI\Entry;
use DI\Scope;

return [

    // Values (not classes)
    'db.host'           => 'localhost',
    'db.port'           => 5000,

    // Indexed non-empty array as value
    'report.recipients' => [
        'bob@acme.example.com',
        'alice@acme.example.com'
    ],

    // Direct mapping (not needed if you didn't disable Reflection)
    'SomeClass'         => Entry::object(),

    // This is not recommended: will instantiate the class even when not used, prevents caching
    'SomeOtherClass'    => new SomeOtherClass(1, "hello"),

    // Defines an instance of My\Class
    'My\Class'          => [
        'host'         => Entry::link('db.host'),
        'otherService' => Entry::link('My\OtherClass'),
    ],

    'My\OtherClass' => [
        'scope'   => Scope::PROTOTYPE(),
        'host'    => Entry::link('db.host'),
        'port'    => Entry::link('db.port'),
        'setFoo1' => Entry::link('My\Foo1'),
        'setFoo2' => [Entry::link('My\Foo1'), Entry::link('My\Foo2')],
        'setFoo3' => [
            'param1' => Entry::link('My\Foo1'),
            'param2' => Entry::link('My\Foo2'),
        ],
        'bar'     => Entry::link('My\Bar'),
    ],

    // Mapping an interface to an implementation
    'My\Interface'      => Entry::object('My\Implementation'),

    // Defining a named instance
    'myNamedInstance'   => Entry::object('My\Class'),

    // Using an anonymous function
    // not recommended: will prevent caching
    'My\Stuff' => Entry::factory(function (Container $c) {
        return new MyClass($c['db.host']);
    }),

];
