<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.io/PHP-DI/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Definition\ClassDefinition;

/**
 * Describe an injection in a class method.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class MethodInjection
{
    /**
     * @var string
     */
    private $methodName;

    /**
     * @var array
     */
    private $parameters = array();

    /**
     * @param string $methodName
     * @param array  $parameters
     */
    public function __construct($methodName, array $parameters = array())
    {
        $this->methodName = (string) $methodName;
        $this->parameters = $parameters;
    }

    /**
     * @return string Method name
     */
    public function getMethodName()
    {
        return $this->methodName;
    }

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
    public function getParameter($index)
    {
        if (! isset($this->parameters[$index])) {
            return null;
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
     * Merge another definition into the current definition.
     *
     * In case of conflicts, the current definition prevails.
     *
     * @param MethodInjection $methodInjection
     */
    public function merge(MethodInjection $methodInjection)
    {
        $this->parameters = $this->parameters + $methodInjection->parameters;
    }
}
