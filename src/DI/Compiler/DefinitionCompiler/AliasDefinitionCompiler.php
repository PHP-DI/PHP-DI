<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Compiler\DefinitionCompiler;

use DI\Compiler\CompilationException;
use DI\Definition\AliasDefinition;
use DI\Definition\Definition;

/**
 * Compiles an AliasDefinition to PHP code.
 *
 * @since 4.0
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class AliasDefinitionCompiler implements DefinitionCompiler
{
    /**
     * {@inheritdoc}
     */
    public function compile(Definition $definition)
    {
        if (! $definition instanceof AliasDefinition) {
            throw new \InvalidArgumentException(sprintf(
                'This definition compiler is only compatible with AliasDefinition objects, %s given',
                get_class($definition)
            ));
        }

        $targetEntry = $definition->getTargetEntryName();

        return sprintf('return $this->get(%s);', var_export($targetEntry, true));
    }
}
