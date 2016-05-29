<?php
namespace PHPSTORM_META {

    $STATIC_METHOD_TYPES = [
        \Interop\Container\ContainerInterface::get('') => [
            "" == "@",
        ],
        \DI\Container::get('') => [
            "" == "@",
        ],
        \EasyMock\EasyMock::easyMock('') => [
            "" == "@",
        ],
        \EasyMock\EasyMock::easySpy('') => [
            "" == "@",
        ],
    ];
}
