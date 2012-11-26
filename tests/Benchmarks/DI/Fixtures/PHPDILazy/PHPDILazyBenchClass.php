<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.com/PHP-DI/
 * @copyright 2012 Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace Benchmarks\DI\Fixtures\PHPDILazy;

use DI\Annotations\Inject;

class PHPDILazyBenchClass
{

	/**
	 * @Inject
	 * @var \Benchmarks\DI\Fixtures\PHPDILazy\PHPDILazyDependency
	 */
	private $service;

	public function test() {
		$this->service->foo();
	}

}
