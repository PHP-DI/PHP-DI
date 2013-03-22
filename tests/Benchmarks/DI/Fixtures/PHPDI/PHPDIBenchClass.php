<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.com/PHP-DI/
 * @copyright 2012 Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace Benchmarks\DI\Fixtures\PHPDI;

use DI\Annotations\Inject;

class PHPDIBenchClass
{

    /**
     * @Inject
     * @var \Benchmarks\DI\Fixtures\PHPDI\PHPDIDependency
     */
    private $service;

    public function test()
    {
        $this->service->foo();
    }

}
