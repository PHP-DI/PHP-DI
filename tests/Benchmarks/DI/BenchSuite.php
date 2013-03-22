<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.com/PHP-DI/
 * @copyright 2012 Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace Benchmarks\DI;

require_once __DIR__ . '/../../../vendor/autoload.php';

/**
 * Bench suite
 */
class BenchSuite extends \PHPBench\BenchSuite
{

    protected $_path = __DIR__;

    public function getBenchCases()
    {
        return array(
            new SimpleInjectBench(),
            new InjectWithCacheBench(),
        );
    }
}

$benchRunner = new \PHPBench\Runner();
$benchRunner->enableLogToFile(true);
$benchRunner->run(new BenchSuite());
