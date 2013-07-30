<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.io/PHP-DI/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace UnitTests\DI\Definition\Source\Annotation\Fixtures;


use UnitTests\DI\Definition\Source\Annotation\Fixtures\Subspace\SomeDependencyFixture2 as SomeDependencyFixture;

/**
 * Has a dependency locally aliased to a name in the local namespace of the targets
 *
 * Class TargetFixture2
 * @package UnitTests\DI\Definition\Source\Annotation\Fixtures
 */
class TargetFixture2
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
