<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.com/PHP-DI/
 * @copyright 2012 Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace IntegrationTests\DI\Fixtures\ValueInjectionTest;

use DI\Annotations\Inject;

/**
 * Fixture class
 */
class ValueInjectionClass
{

    /**
     * @Inject("db.host")
     * @var string
     */
    private $value;

    /**
     * Inject the dependencies
     */
    public function __construct()
    {
        \DI\Container::getInstance()->injectAll($this);
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

}
