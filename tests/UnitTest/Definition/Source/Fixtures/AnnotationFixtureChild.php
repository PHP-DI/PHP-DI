<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Test\UnitTest\Definition\Source\Fixtures;

use DI\Annotation\Inject;

/**
 * Used to check that child classes also have the injections of the parent classes.
 */
class AnnotationFixtureChild extends AnnotationFixtureParent
{
    /**
     * @Inject("foo")
     */
    protected $propertyChild;

    /**
     * @Inject
     */
    public function methodChild()
    {
    }
}
