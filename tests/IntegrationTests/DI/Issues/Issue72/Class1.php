<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace IntegrationTests\DI\Issues\Issue72;

use DI\Annotation\Inject;

class Class1
{
    public $arg1;

    /**
     * @Inject({"service1"})
     */
    public function __construct(\stdClass $arg1)
    {
        $this->arg1 = $arg1;
    }
}
