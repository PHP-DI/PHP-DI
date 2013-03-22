<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.com/PHP-DI/
 * @copyright 2012 Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace Benchmarks\DI\Fixtures\Singleton;

class SingletonBenchClass
{

    public function test()
    {
        $service = SingletonDependency::getInstance();
        $service->foo();
    }

}
