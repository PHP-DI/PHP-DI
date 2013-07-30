<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.io/PHP-DI/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace UnitTests\DI\Definition\Source\Annotation\Fixtures;


/**
 * Has a dependency in the local namespace
 *
 * Class TargetFixture3
 * @package UnitTests\DI\Definition\Source\Annotation\Fixtures
 */
class TargetFixture3 extends \PHPUnit_Framework_TestCase
{

    /**
     * @var SomeDependencyFixture
     */
    protected $dependency1;


    /**
     * @var Subspace\SomeDependencyFixture2
     */
    protected $dependency2;


    /**
     * @param SomeDependencyFixture $dependency1
     * @param Subspace\SomeDependencyFixture2 $dependency2
     */
    public function SomeMethod($dependency1, $dependency2)
    {

    }

}
