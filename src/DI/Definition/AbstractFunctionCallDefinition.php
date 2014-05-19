<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.io/PHP-DI/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Definition;

/**
 * Describe a function call.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
abstract class AbstractFunctionCallDefinition
{
    /**
     * @var array
     */
    protected $parameters = array();

    /**
     * @return \ReflectionFunctionAbstract
     */
    abstract public function getReflection();

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
}
