<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Test\UnitTest\Definition\Source\Fixtures;

class AnnotationFixture3
{
    /**
     * @Inject
     * @param AnnotationFixture2 $param1
     * @param string             $param2
     */
    public function method1($param1, $param2)
    {
    }
}
