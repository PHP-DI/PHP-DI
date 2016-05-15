<?php

use DI\ContainerBuilder;

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/get/GetFixture.php';

$builder = new ContainerBuilder();
$builder->useAutowiring(true);
$builder->useAnnotations(false);
$builder->addDefinitions(__DIR__ . '/get/config.php');
$container = $builder->build();

$container->get('object');
$container->get('value');
$container->get('string');
$container->get('alias');
$container->get('factory');
$container->get('array');
