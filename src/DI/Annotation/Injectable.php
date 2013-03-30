<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.com/PHP-DI/
 * @copyright 2012 Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Annotation;

use DI\Scope;

/**
 * "Injectable" annotation
 *
 * Marks a class as injectable
 *
 * @Annotation
 * @Target("CLASS")
 *
 * @author Domenic Muskulus <domenic@muskulus.eu>
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
final class Injectable
{

    /**
     * The scope of an class: prototype, singleton
     * @var Scope|null
     */
    private $scope;

    /**
     * @param array $values
     */
    public function __construct(array $values)
    {
        if (isset($values['scope'])) {
            $this->scope = new Scope($values['scope']);
        }
    }

    /**
     * @return Scope|null
     */
    public function getScope()
    {
        return $this->scope;
    }

}
