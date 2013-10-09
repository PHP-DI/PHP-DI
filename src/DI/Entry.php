<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.io/PHP-DI/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI;

use DI\Definition\EntryReference;
use DI\DefinitionHelper\CallableDefinitionHelper;
use DI\DefinitionHelper\ObjectDefinitionHelper;

/**
 * Helps defining a container entry
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class Entry
{
    /**
     * @param string|null $className
     * @return ObjectDefinitionHelper
     */
    public static function object($className = null)
    {
        return new ObjectDefinitionHelper($className);
    }

    /**
     * @param string $entryName
     * @return EntryReference
     */
    public static function link($entryName)
    {
        return new EntryReference($entryName);
    }

    /**
     * @param string|callable $callable Can be either the name of a static method, or a callable
     * @return CallableDefinitionHelper
     */
    public static function factory($callable)
    {
        return new CallableDefinitionHelper($callable);
    }
}
