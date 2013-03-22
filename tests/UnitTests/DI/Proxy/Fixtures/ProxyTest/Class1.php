<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.com/PHP-DI/
 * @copyright 2012 Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace UnitTests\DI\Proxy\Fixtures\ProxyTest;

/**
 * Fixture class
 */
class Class1
{

    public $property1 = true;

    /**
     * @return bool
     */
    public function getTrue()
    {
        return true;
    }

    /**
     * The __invoke() method is called when a script tries to call an object as a function
     * @return mixed
     * @see http://www.php.net/manual/en/language.oop5.magic.php#object.invoke
     */
    public function __invoke()
    {
        return true;
    }

    /**
     * Convert the object to string
     */
    public function __toString()
    {
        return "1";
    }

}
