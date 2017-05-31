<?php

use DI\ContainerBuilder;
use DI\Scope;
use Psr\Container\ContainerInterface;

require_once __DIR__ . '/vendor/autoload.php';

$builder = new ContainerBuilder();
$builder->useAutowiring(false);
$builder->useAnnotations(false);
$builder->compile(__DIR__ . '/tmp/factory.php');

$builder->addDefinitions([

    'stdClass' => new stdClass(),

    'empty' => DI\factory(function () {
        return null;
    })->scope(Scope::PROTOTYPE),

    'container' => DI\factory(function (ContainerInterface $c) {
        return null;
    })->scope(Scope::PROTOTYPE),

    'entry' => DI\factory(function (ContainerInterface $c) {
        return $c->get('stdClass');
    })->scope(Scope::PROTOTYPE),

]);

$container = $builder->build();

for ($i = 0; $i < 100; $i++) {
    $container->get('empty');
    $container->get('container');
    $container->get('entry');
}
