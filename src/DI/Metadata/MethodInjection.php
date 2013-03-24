<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.com/PHP-DI/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Metadata;

/**
 * Describe an injection in a class method
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class MethodInjection
{

    /**
     * Method name
     * @var string
     */
    private $methodName;

    /**
     * @var ParameterInjection[]
     */
    private $parameterInjections;

    /**
     * @param string $methodName
     * @param array  $parameterInjections
     */
    public function __construct($methodName, array $parameterInjections = array())
    {
        $this->methodName = (string) $methodName;
        $this->parameterInjections = $parameterInjections;
    }

    /**
     * @return string Method name
     */
    public function getMethodName()
    {
        return $this->methodName;
    }

    /**
     * @return ParameterInjection[]
     */
    public function getParameterInjections()
    {
        return $this->parameterInjections;
    }

    /**
     * @param ParameterInjection $parameterInjection
     */
    public function addParameterInjection(ParameterInjection $parameterInjection)
    {
        $this->parameterInjections[] = $parameterInjection;
    }

}
