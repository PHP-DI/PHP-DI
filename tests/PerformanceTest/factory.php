<?php

use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;

require_once __DIR__ . '/vendor/autoload.php';

$builder = new ContainerBuilder();
$builder->useAutowiring(false);
$builder->useAnnotations(false);

$builder->addDefinitions([

    'stdClass' => new stdClass(),

    'empty' => DI\factory(function () {
        return null;
    }),

    'container' => DI\factory(function (ContainerInterface $c) {
        return null;
    }),

    'entry' => DI\factory(function (ContainerInterface $c) {
        return $c->get('stdClass');
    }),

]);

$container = $builder->build();

for ($i = 0; $i < 100; $i++) {
    $container->get('empty');
    $container->get('container');
    $container->get('entry');
}
