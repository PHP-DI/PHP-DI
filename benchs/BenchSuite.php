<?php

require_once __DIR__.'/../vendor/autoload.php';

/**
 * Bench suite
 */
class BenchSuite extends \PHPBench\BenchSuite
{
	protected $_path = __DIR__;
}

$benchRunner = new \PHPBench\Runner();
$benchRunner->enableLogToFile(true);
$benchRunner->run(new BenchSuite());
