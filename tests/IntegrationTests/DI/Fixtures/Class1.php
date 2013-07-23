<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.io/PHP-DI/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace IntegrationTests\DI\Fixtures;

use DI\Annotation\Inject;
use DI\Annotation\Injectable;

/**
 * Fixture class
 * @Injectable(scope="prototype")
 */
class Class1
{

    /**
     * @Inject
     * @var Class2
     */
    public $property1;

    /**
     * @Inject
     * @var Interface1
     */
    public $property2;

    /**
     * @Inject("namedDependency")
     */
    public $property3;

    /**
     * @Inject(name="foo")
     */
    public $property4;

    /**
     * @Inject(lazy=true)
     * @var LazyDependency
     */
    public $property5;

    public $constructorParam1;
    public $constructorParam2;

    public $method1Param1;

    public $method2Param1;

    public $method3Param1;
    public $method3Param2;

    public $method4Param1;

    /**
     * @param Class2     $param1
     * @param Interface1 $param2
     */
    public function __construct(Class2 $param1, Interface1 $param2)
    {
        $this->constructorParam1 = $param1;
        $this->constructorParam2 = $param2;
    }

    /**
     * @Inject
     * @param Class2 $param1
     */
    public function method1(Class2 $param1)
    {
        $this->method1Param1 = $param1;
    }

    /**
     * @Inject
     * @param Interface1 $param1
     */
    public function method2(Interface1 $param1)
    {
        $this->method2Param1 = $param1;
    }

    /**
     * @Inject({"namedDependency", "foo"})
     * @param string $param1
     */
    public function method3($param1, $param2)
    {
        $this->method3Param1 = $param1;
        $this->method3Param2 = $param2;
    }

    /**
     * @Inject({"param1" = {"lazy" = true}})
     * @param string $param1
     */
    public function method4(LazyDependency $param1)
    {
        $this->method4Param1 = $param1;
    }

}
