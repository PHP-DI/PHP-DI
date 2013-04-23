<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.io/PHP-DI/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI;

use MyCLabs\Enum\Enum;

/**
 * Scope enum
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class Scope extends Enum
{

    const SINGLETON = 'singleton';
    const PROTOTYPE = 'prototype';

    /**
     * @return Scope
     */
    public static function SINGLETON()
    {
        return new static(self::SINGLETON);
    }

    /**
     * @return Scope
     */
    public static function PROTOTYPE()
    {
        return new static(self::PROTOTYPE);
    }

}
