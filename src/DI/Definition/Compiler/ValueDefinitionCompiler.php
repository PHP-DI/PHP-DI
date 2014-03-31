<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Definition\Compiler;

use DI\Compiler\CompilationException;
use DI\Definition\Definition;
use DI\Definition\ValueDefinition;

/**
 * Compiles a ValueDefinition to PHP code.
 *
 * @since 4.0
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class ValueDefinitionCompiler implements DefinitionCompiler
{
    /**
     * {@inheritdoc}
     */
    public function compile(Definition $definition)
    {
        if (! $definition instanceof ValueDefinition) {
            throw new \InvalidArgumentException(sprintf(
                'This definition compiler is only compatible with ValueDefinition objects, %s given',
                get_class($definition)
            ));
        }

        $value = $definition->getValue();

        // We don't compile instances
        if (is_object($value)) {
            throw new CompilationException(
                "Impossible to compile objects to PHP code, use a callback (factory) or a class definition instead"
            );
        }

        $code = sprintf('return %s;', var_export($value, true));

        return $code;
    }
}
