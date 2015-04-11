<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Definition\Dumper;

use DI\Debug;
use DI\Definition\Definition;
use DI\Definition\EntryReference;
use DI\Definition\EnvironmentVariableDefinition;
use DI\Definition\Helper\DefinitionHelper;

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

            if ($defaultValue instanceof DefinitionHelper) {
                $nestedDefinition = Debug::dumpDefinition($defaultValue->getDefinition(''));
                $defaultValueStr = $this->indent($nestedDefinition);
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

    private function indent($str)
    {
        return str_replace("\n", "\n    ", $str);
    }
}
