<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace UnitTests\DI\Annotation\Fixtures;

use Foo\Bar\SomeUnknownImportedAnnotation;

class MixedAnnotationsFixture
{
    /**
     * @Inject("foo")
     * @SomeRandomAnnotation
     * @SomeUnknownImportedAnnotation
     */
    protected $property1;
}
