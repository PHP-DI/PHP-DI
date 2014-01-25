<?php

return array(
    'service3' => \DI\factory(function () {
        $value = new \stdClass();
        $value->foo = 'baz';
        return $value;
    }),
    'IntegrationTests\DI\Issues\Issue72\Class1' => \DI\object()
            ->constructor(\DI\link('service3')),
);
