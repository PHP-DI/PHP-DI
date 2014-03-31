<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Definition\Helper;

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
     * @param callable $factory
     */
    public function __construct($factory)
    {
        $this->factory = $factory;
    }

    /**
     * @param string $entryName Container entry name
     * @return FactoryDefinition
     */
    public function getDefinition($entryName)
    {
        return new FactoryDefinition($entryName, $this->factory);
    }
}
