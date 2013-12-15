<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.io/PHP-DI/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Definition\Helper;

use DI\Definition\CallableDefinition;

/**
 * Helps defining how to create an instance of a class using a callable.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class CallableDefinitionHelper implements DefinitionHelper
{
    /**
     * @var callable
     */
    private $callable;

    /**
     * @param callable $callable
     */
    public function __construct($callable)
    {
        $this->callable = $callable;
    }

    /**
     * @param string $entryName Container entry name
     * @return CallableDefinition
     */
    public function getDefinition($entryName)
    {
        return new CallableDefinition($entryName, $this->callable);
    }
}
