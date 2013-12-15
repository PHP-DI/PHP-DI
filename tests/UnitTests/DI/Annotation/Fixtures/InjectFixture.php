<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.io/PHP-DI/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace UnitTests\DI\Annotation\Fixtures;

use DI\Annotation\Inject;

class InjectFixture
{
    /**
     * @Inject("foo")
     */
    protected $property1;

    /**
     * @Inject
     * @var Dependency
     */
    protected $property2;

    /**
     * @Inject(name="foo")
     */
    protected $property3;

    /**
     * @Inject
     */
    public function method1()
    {
    }

    /**
     * @Inject({"foo", "bar"})
     */
    public function method2($str1, $str2)
    {
    }

    /**
     * @Inject({"str1" = "foo"})
     */
    public function method3($str1)
    {
    }
}
