<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Definition\Compiler;

use DI\Definition\Definition;

/**
 * Object that compiles a definition to PHP code for best performances.
 *
 * @since 4.0
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
interface DefinitionCompiler
{
    /**
     * Compile a definition to PHP code.
     *
     * @param Definition $definition Object that defines how the value should be obtained.
     *
     * @return string PHP code able to resolve the definition.
     */
    public function compile(Definition $definition);
}
