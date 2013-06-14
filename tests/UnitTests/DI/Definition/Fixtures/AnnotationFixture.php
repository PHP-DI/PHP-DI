<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.io/PHP-DI/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace UnitTests\DI\Definition\Fixtures;

use DI\Annotation\Inject;

class AnnotationFixture
{

    /**
     * @Inject("foo")
     */
    protected $property1;

    /**
     * @Inject
     * @var AnnotationFixture2
     */
    protected $property2;

    /**
     * @Inject(name="foo", lazy=true)
     */
    protected $property3;

    /**
     * @Inject({"foo", "bar"})
     */
    public function __construct($param1, $param2)
    {
    }

    /**
     * @Inject
     */
    public function method1()
    {
    }

    /**
     * @Inject({"foo", "bar"})
     */
    public function method2($param1, $param2)
    {
    }

    /**
     * @Inject({
     *  {"name" = "foo"},
     *  "bar"
     * })
     */
    public function method3($param1, $param2)
    {
    }

    /**
     * @Inject({"param2" = "bar"})
     */
    public function method4($param1, $param2)
    {
    }

    /**
     * @Inject
     * @param $param1
     * @param AnnotationFixture2 $param2
     */
    public function method5(AnnotationFixture2 $param1, $param2)
    {
    }

    /**
     * @Inject({"foo", "bar"})
     * @param AnnotationFixture2 $param1
     * @param AnnotationFixture2 $param2
     */
    public function method6($param1, $param2)
    {
    }

    /**
     * @Inject({
     *   "param1" = {"name" = "foo", "lazy" = true},
     *   "param2" = {"name" = "bar"}
     * })
     */
    public function method7($param1, $param2)
    {
    }

}
