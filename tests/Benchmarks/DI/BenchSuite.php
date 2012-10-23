<?php

namespace Benchmarks\DI;

require_once __DIR__ . '/../../../vendor/autoload.php';

/**
 * Bench suite
 */
class BenchSuite extends \PHPBench\BenchSuite
{
	protected $_path = __DIR__;

	public function getBenchCases() {
		return array(new SimpleInjectBench());
	}
}

$benchRunner = new \PHPBench\Runner();
$benchRunner->enableLogToFile(true);
$benchRunner->run(new BenchSuite());
