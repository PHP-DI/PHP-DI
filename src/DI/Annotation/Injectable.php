<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
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
     * Should the object be lazy-loaded
     * @var boolean|null
     */
    private $lazy;

    /**
     * @param array $values
     */
    public function __construct(array $values)
    {
        if (isset($values['scope'])) {
            $this->scope = new Scope($values['scope']);
        }
        if (isset($values['lazy'])) {
            $this->lazy = (boolean) $values['lazy'];
        }
    }

    /**
     * @return Scope|null
     */
    public function getScope()
    {
        return $this->scope;
    }

    /**
     * @return boolean|null
     */
    public function isLazy()
    {
        return $this->lazy;
    }
}
