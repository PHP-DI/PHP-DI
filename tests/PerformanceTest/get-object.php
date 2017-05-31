<?php

use DI\ContainerBuilder;

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/get-object/GetFixture.php';

$builder = new ContainerBuilder();
$builder->useAutowiring(true);
$builder->useAnnotations(false);
$builder->addDefinitions(__DIR__ . '/get-object/config.php');
$builder->compile(__DIR__ . '/tmp/get-object.php');
$container = $builder->build();

$container->get('object1');
$container->get('object2');
$container->get('object3');
$container->get('object4');
$container->get('object5');
$container->get('object6');
$container->get('object7');
$container->get('object8');
$container->get('object9');
$container->get('object10');
