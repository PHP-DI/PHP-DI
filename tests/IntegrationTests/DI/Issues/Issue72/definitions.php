<?php

return array(
    'service2' => \DI\factory(function () {
        $value = new \stdClass();
        $value->foo = 'bar';
        return $value;
    }),
    'IntegrationTests\DI\Issues\Issue72\Class1' => \DI\object()
            ->withConstructor(\DI\link('service2')),
);
