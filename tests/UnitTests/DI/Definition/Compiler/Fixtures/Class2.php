<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.com/PHP-DI/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace UnitTests\DI\Definition\Compiler\Fixtures;

class Class2
{
    public function setThing()
    {
    }

    public function setWithParams($param1, $param2)
    {
    }

    public function setWithDefaultValues($param1 = 'foo', $param2 = 'bar')
    {
    }
}
