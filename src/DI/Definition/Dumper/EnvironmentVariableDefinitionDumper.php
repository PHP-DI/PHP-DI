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
use DI\Definition\EntryReference;
use DI\Definition\EnvironmentVariableDefinition;

/**
 * Dumps environment variable definitions.
 *
 * @author James Harris <james.harris@icecave.com.au>
 */
class EnvironmentVariableDefinitionDumper implements DefinitionDumper
{
    /**
     * {@inheritdoc}
     */
    public function dump(Definition $definition)
    {
        if (! $definition instanceof EnvironmentVariableDefinition) {
            throw new \InvalidArgumentException(sprintf(
                'This definition dumper is only compatible with EnvironmentVariableDefinition objects, %s given',
                get_class($definition)
            ));
        }

        $str = "    variable = " . $definition->getVariableName();
        $str .= "\n    optional = " . ($definition->isOptional() ? 'yes' : 'no');

        if ($definition->isOptional()) {
            $defaultValue = $definition->getDefaultValue();

            if ($defaultValue instanceof EntryReference) {
                $defaultValueStr = sprintf('link(%s)', $defaultValue->getName());
            } else {
                $defaultValueStr = var_export($defaultValue, true);
            }

            $str .= "\n    default = " . $defaultValueStr;
        }

        return sprintf(
            "Environment variable (\n%s\n)",
            $str
        );
    }
}
