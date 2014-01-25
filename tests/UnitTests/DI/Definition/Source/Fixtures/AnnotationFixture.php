<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace UnitTests\DI\Definition\Source\Fixtures;

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
     * @Inject(name="foo")
     */
    protected $property3;

    protected $unannotatedProperty;

    /**
     * Static property shouldn't be injected
     *
     * @Inject("foo")
     */
    protected static $staticProperty;

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
     * @Inject
     * @param $param1
     * @param AnnotationFixture2 $param2
     */
    public function method3(AnnotationFixture2 $param1, $param2)
    {
    }

    /**
     * @Inject({"foo", "bar"})
     * @param AnnotationFixture2 $param1
     * @param AnnotationFixture2 $param2
     */
    public function method4($param1, $param2)
    {
    }

    /**
     * Indexed by name, param1 not specified:
     * @Inject({"param2" = "bar"})
     */
    public function method5($param1, $param2)
    {
    }

    public function unannotatedMethod()
    {
    }

    /**
     * @Inject
     */
    public static function staticMethod()
    {
    }
}
