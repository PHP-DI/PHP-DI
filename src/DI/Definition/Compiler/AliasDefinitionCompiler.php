<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Definition\Compiler;

use DI\Definition\AliasDefinition;
use DI\Definition\Definition;

/**
 * Compiles an AliasDefinition to PHP code.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class AliasDefinitionCompiler implements DefinitionCompiler
{
    /**
     * @param AliasDefinition $definition
     *
     * {@inheritdoc}
     */
    public function compile(Definition $definition)
    {
        $targetEntry = $definition->getTargetEntryName();

        return sprintf('return $this->get(%s);', var_export($targetEntry, true));
    }
}
