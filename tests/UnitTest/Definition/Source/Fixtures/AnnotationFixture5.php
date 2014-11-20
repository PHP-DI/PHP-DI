<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Test\UnitTest\Definition\Source\Fixtures;

class AnnotationFixture5
{
    /**
     * @Inject
     * @var foobar
     */
    public $property;

    /**
     * @param foobar $foo
     */
    public function __construct($foo)
    {
    }
}
