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
use DI\Definition\ValueDefinition;

/**
 * Resolves a value definition to a value.
 *
 * @since 4.0
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class ValueDefinitionResolver implements DefinitionResolver
{
    /**
     * Resolve a value definition to a value.
     *
     * A value definition is simple, so this will just return the value of the ValueDefinition.
     *
     * {@inheritdoc}
     */
    public function resolve(Definition $definition, array $parameters = array())
    {
        if (! $definition instanceof ValueDefinition) {
            throw new \InvalidArgumentException(sprintf(
                'This definition resolver is only compatible with ValueDefinition objects, %s given',
                get_class($definition)
            ));
        }

        return $definition->getValue();
    }
}
