<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Definition\Resolver;

use DI\Definition\Definition;

/**
 * Resolves a definition to a value.
 *
 * @since 4.0
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
interface DefinitionResolver
{
    /**
     * Resolve a definition to a value.
     *
     * @param Definition $definition Object that defines how the value should be obtained.
     * @param array      $parameters Optional parameters to use to build the entry.
     *
     * @return mixed Value obtained from the definition.
     */
    public function resolve(Definition $definition, array $parameters = array());
}
