<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Definition;

/**
 * Describe a function call.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class FunctionCallDefinition extends AbstractFunctionCallDefinition
{
    /**
     * @var callable
     */
    private $callable;

    /**
     * @param callable $callable
     * @param array    $parameters
     */
    public function __construct($callable, array $parameters = array())
    {
        $this->callable = $callable;
        $this->parameters = $parameters;
    }

    /**
     * {@inheritdoc}
     */
    public function getCallable()
    {
        return $this->callable;
    }
}
