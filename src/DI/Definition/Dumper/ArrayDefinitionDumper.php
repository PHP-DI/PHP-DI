<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Definition\Dumper;

use DI\Definition\ArrayDefinition;
use DI\Definition\Definition;
use DI\Definition\EntryReference;

/**
 * Dumps array definitions.
 *
 * @since 5.0
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class ArrayDefinitionDumper implements DefinitionDumper
{
    /**
     * {@inheritdoc}
     */
    public function dump(Definition $definition)
    {
        if (! $definition instanceof ArrayDefinition) {
            throw new \InvalidArgumentException(sprintf(
                'This definition dumper is only compatible with ArrayDefinition objects, %s given',
                get_class($definition)
            ));
        }

        $str = '[' . PHP_EOL;

        foreach ($definition->getValues() as $key => $value) {
            if (is_string($key)) {
                $key = '"' . $key . '"';
            }

            $str .= '    ' . $key . ' => ';

            if ($value instanceof EntryReference) {
                $str .= 'link(' . $value->getName() . ')';
            } else {
                $str .= $this->dumpVariable($value);
            }

            $str .= ',' . PHP_EOL;
        }

        $str .= ']';

        return $str;
    }

    private function dumpVariable($var)
    {
        ob_start();

        var_dump($var);

        return trim(ob_get_clean());
    }
}
