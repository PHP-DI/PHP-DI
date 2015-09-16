<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Test\IntegrationTest\Annotations;

use DI\Annotation\Inject;

class B
{
    /**
     * @Inject
     * @var A
     */
    public $public;

    /**
     * @Inject
     * @var A
     */
    protected $protected;

    /**
     * @Inject
     * @var A
     */
    private $private;

    public function getProtected()
    {
        return $this->protected;
    }

    public function getPrivate()
    {
        return $this->private;
    }
}
