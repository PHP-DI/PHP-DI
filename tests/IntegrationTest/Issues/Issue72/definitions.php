<?php

return [
    'service2' => \DI\factory(function () {
        $value = new \stdClass();
        $value->foo = 'bar';

        return $value;
    }),
    'DI\Test\IntegrationTest\Issues\Issue72\Class1' => \DI\create()
            ->constructor(\DI\get('service2')),
];
