<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.io/PHP-DI/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Definition\Exception;

use DI\Debug;
use DI\Definition\Definition;

/**
 * Invalid DI definitions
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class DefinitionException extends \Exception
{
    public static function create(Definition $definition, $message)
    {
        return new self(sprintf(
            "Entry %s cannot be resolved: %s\nDefinition of %s:\n%s",
            $definition->getName(),
            $message,
            $definition->getName(),
            Debug::dump($definition)
        ));
    }
}
