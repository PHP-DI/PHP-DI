<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Definition\Helper;

use DI\Definition\DecoratorDefinition;
use DI\Definition\FactoryDefinition;

/**
 * Helps defining how to create an instance of a class using a factory (callable).
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class FactoryDefinitionHelper implements DefinitionHelper
{
    /**
     * @var callable
     */
    private $factory;

    /**
     * @var string|null
     */
    private $scope;

    /**
     * @var bool
     */
    private $decorate;

    /**
     * @param callable $factory
     * @param bool     $decorate Is the factory decorating a previous definition?
     */
    public function __construct($factory, $decorate = false)
    {
        $this->factory = $factory;
        $this->decorate = $decorate;
    }

    /**
     * Defines the scope of the entry.
     *
     * @param string $scope
     *
     * @return FactoryDefinitionHelper
     */
    public function scope($scope)
    {
        $this->scope = $scope;
        return $this;
    }

    /**
     * @param string $entryName Container entry name
     * @return FactoryDefinition
     */
    public function getDefinition($entryName)
    {
        if ($this->decorate) {
            return new DecoratorDefinition($entryName, $this->factory, $this->scope);
        }

        return new FactoryDefinition($entryName, $this->factory, $this->scope);
    }
}
