<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.io/PHP-DI/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Definition\ClassInjection;

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
     * Merge another definition into the current definition.
     *
     * In case of conflicts, the latter prevails (i.e. the other definition)
     *
     * @param MethodInjection $methodInjection
     */
    public function merge(MethodInjection $methodInjection)
    {
        $this->parameters = $methodInjection->parameters + $this->parameters;
    }
}
