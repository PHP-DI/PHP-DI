<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.io/PHP-DI/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Definition\Source;

use DI\Definition\Definition;

/**
 * Simple container of Definitions
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class SimpleDefinitionSource implements DefinitionSource
{

    /**
     * @var Definition[]
     */
    private $definitions = array();

    /**
     * {@inheritdoc}
     */
    public function getDefinition($name)
    {
        if (array_key_exists($name, $this->definitions)) {
            return $this->definitions[$name];
        }

        return null;
    }

    /**
     * Add a definition
     *
     * @param string     $name
     * @param Definition $definition
     */
    public function addDefinition(Definition $definition)
    {
        $this->definitions[$definition->getName()] = $definition;
    }

}
