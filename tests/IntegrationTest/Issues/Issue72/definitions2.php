<?php

return array(
    'service3' => \DI\factory(function () {
        $value = new \stdClass();
        $value->foo = 'baz';
        return $value;
    }),
    'DI\Test\IntegrationTest\Issues\Issue72\Class1' => \DI\object()
            ->constructor(\DI\get('service3')),
);
