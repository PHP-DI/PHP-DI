<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Definition\Dumper;

use DI\Definition\AliasDefinition;
use DI\Definition\Definition;

/**
 * Dumps alias definitions.
 *
 * @since 4.1
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class AliasDefinitionDumper implements DefinitionDumper
{
    /**
     * {@inheritdoc}
     */
    public function dump(Definition $definition)
    {
        if (! $definition instanceof AliasDefinition) {
            throw new \InvalidArgumentException(sprintf(
                'This definition dumper is only compatible with AliasDefinition objects, %s given',
                get_class($definition)
            ));
        }

        return sprintf(
            "Alias (\n    %s => %s\n)",
            $definition->getName(),
            $definition->getTargetEntryName()
        );
    }
}
