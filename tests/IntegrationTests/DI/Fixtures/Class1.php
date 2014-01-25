<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
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
     * @Inject
     * @var LazyDependency
     */
    public $property5;

    public $constructorParam1;
    public $constructorParam2;
    public $constructorParam3;

    public $method1Param1;

    public $method2Param1;

    public $method3Param1;
    public $method3Param2;

    public $method4Param1;

    public $method5Param1;
    public $method5Param2;

    /**
     * @param Class2         $param1
     * @param Interface1     $param2
     * @param LazyDependency $param3
     * @throws \Exception
     */
    public function __construct(Class2 $param1, Interface1 $param2, LazyDependency $param3, $optional = true)
    {
        $this->constructorParam1 = $param1;
        $this->constructorParam2 = $param2;
        $this->constructorParam3 = $param3;

        if ($optional !== true) {
            throw new \Exception("Expected optional parameter to not be defined");
        }
    }

    /**
     * Tests optional parameter is not overridden.
     *
     * @Inject
     * @param Class2 $param1
     * @throws \Exception
     */
    public function method1(Class2 $param1, $optional = true)
    {
        $this->method1Param1 = $param1;

        if ($optional !== true) {
            throw new \Exception("Expected optional parameter to not be defined");
        }
    }

    /**
     * Tests automatic resolution of parameter based on the type-hinting.
     *
     * @Inject
     * @param Interface1 $param1
     */
    public function method2(Interface1 $param1)
    {
        $this->method2Param1 = $param1;
    }

    /**
     * Tests defining parameters.
     *
     * @Inject({"namedDependency", "foo"})
     * @param string $param1
     */
    public function method3($param1, $param2)
    {
        $this->method3Param1 = $param1;
        $this->method3Param2 = $param2;
    }

    /**
     * Tests injecting a lazy dependency.
     *
     * @Inject
     * @param LazyDependency $param1
     */
    public function method4(LazyDependency $param1)
    {
        $this->method4Param1 = $param1;
    }

    /**
     * Tests defining a parameter by its name.
     *
     * @Inject({"param2" = "foo"})
     */
    public function method5(Interface1 $param1, $param2)
    {
        $this->method5Param1 = $param1;
        $this->method5Param2 = $param2;
    }
}
