<?php

declare(strict_types=1);

return [
    'service3' => \DI\factory(function () {
        $value = new \stdClass();
        $value->foo = 'baz';

        return $value;
    }),
    'DI\Test\IntegrationTest\Issues\Issue72\Class1' => \DI\create()
            ->constructor(\DI\get('service3')),
];
