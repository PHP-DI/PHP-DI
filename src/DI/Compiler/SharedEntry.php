<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Compiler;

/**
 * Shared container entry (singleton entry).
 */
class SharedEntry
{
    /**
     * @var mixed Entry value.
     */
    private $value;

    public function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * Returns the entry value.
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }
}
