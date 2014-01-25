<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI;

use MyCLabs\Enum\Enum;

/**
 * Scope enum.
 *
 * The scope defines the lifecycle of an entry.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class Scope extends Enum
{
    const SINGLETON = 'singleton';
    const PROTOTYPE = 'prototype';

    /**
     * A singleton entry will be computed once and shared.
     * For a class, only a single instance of the class will be created.
     *
     * @return Scope
     */
    public static function SINGLETON()
    {
        return new static(self::SINGLETON);
    }

    /**
     * A prototype entry will be recomputed each time it is asked.
     * For a class, this will create a new instance each time.
     *
     * @return Scope
     */
    public static function PROTOTYPE()
    {
        return new static(self::PROTOTYPE);
    }

    /**
     * Exports the object to valid PHP code.
     *
     * @return string
     */
    public function exportToPHP()
    {
        return sprintf('new \DI\Scope(%s)', var_export($this->value, true));
    }
}
