<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Definition\Source;

use DI\Definition\Definition;
use DI\Definition\Exception\DefinitionException;

/**
 * Source of definitions for callables.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
interface CallableDefinitionSource
{
    /**
     * Returns the DI definition for the callable.
     *
     * @param callable $callable
     *
     * @throws DefinitionException An invalid definition was found.
     * @return Definition|null
     */
    public function getCallableDefinition($callable);
}
