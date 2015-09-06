<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Definition\Dumper;

use DI\Definition\Definition;
use DI\Definition\DotNotationDefinition;

/**
 * Dumps dotNotation definitions.
 *
 * @since 5.1
 * @author Michael Seidel <mvs@albami.de>
 */
class DotNotationDefinitionDumper implements DefinitionDumper
{
    /**
     * {@inheritdoc}
     */
    public function dump(Definition $definition)
    {
        if (! $definition instanceof DotNotationDefinition) {
            throw new \InvalidArgumentException(sprintf(
                'This definition dumper is only compatible with DotNotationDefinition objects, %s given',
                get_class($definition)
            ));
        }

        return $definition->getExpression();
    }
}