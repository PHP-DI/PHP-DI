<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.com/PHP-DI/
 * @copyright 2012 Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Definition;

use DI\Scope;

/**
 * Reads DI definitions from a PHP array
 */
class ArrayDefinitionReader implements DefinitionReader
{

    /**
     * DI definitions in a PHP array
     * @var array
     */
    private $definitions = array();

    /**
     * {@inheritdoc}
     */
    public function getDefinition($name)
    {
        $definition = null;

        if (array_key_exists($name, $this->definitions)) {
            $arrayDefinition = $this->definitions[$name];

            // It's a class
            $definition = new ClassDefinition($name);

            // Class scope
            if (array_key_exists('scope', $arrayDefinition)) {
                $scope = $arrayDefinition['scope'];
                if (!$scope instanceof Scope) {
                    $scope = new Scope($scope);
                }
                $definition->setScope($scope);
            }

            // Lazy
            if (array_key_exists('lazy', $arrayDefinition)) {
                $definition->setLazy($arrayDefinition['lazy']);
            }
        }

        return $definition;
    }

    /**
     * @param array $definitions DI definitions in a PHP array
     */
    public function addDefinitions(array $definitions)
    {
        // The newly added data prevails
        // "for keys that exist in both arrays, the elements from the left-hand array will be used"
        $this->definitions = $definitions + $this->definitions;
    }

}
