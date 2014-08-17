<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Definition;

use DI\Definition\Exception\DefinitionException;
use DI\Scope;

/**
 * Describe a function call.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
abstract class AbstractFunctionCallDefinition implements Definition, MergeableDefinition
{
    /**
     * @var array
     */
    protected $parameters = array();

    /**
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @param int $index Position of the parameter (starting at 0)
     * @return mixed|null Value to inject, or null if no injection defined.
     */
    public function hasParameter($index)
    {
        return array_key_exists($index, $this->parameters);
    }

    /**
     * @param int $index Position of the parameter (starting at 0)
     * @throws \InvalidArgumentException
     * @return mixed Value to inject
     */
    public function getParameter($index)
    {
        if (! array_key_exists($index, $this->parameters)) {
            throw new \InvalidArgumentException('There is no parameter value for index ' . $index);
        }

        return $this->parameters[$index];
    }

    /**
     * Replace the parameters of the definition by a new array of parameters.
     *
     * @param array $parameters
     */
    public function replaceParameters(array $parameters)
    {
        $this->parameters = $parameters;
    }

    /**
     * {@inheritdoc}
     */
    public function merge(MergeableDefinition $definition)
    {
        if (!$definition instanceof AbstractFunctionCallDefinition) {
            throw new DefinitionException(
                "DI definition conflict: trying to merge incompatible definitions"
            );
        }

        $this->parameters = $this->parameters + $definition->parameters;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getScope()
    {
        return Scope::PROTOTYPE();
    }
}
