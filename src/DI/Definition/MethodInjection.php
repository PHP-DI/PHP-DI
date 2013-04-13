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
    private $parameterInjections = array();

    /**
     * @param string               $methodName
     * @param ParameterInjection[] $parameterInjections
     */
    public function __construct($methodName, array $parameterInjections = array())
    {
        $this->methodName = (string) $methodName;
        foreach ($parameterInjections as $parameterInjection) {
            $this->addParameterInjection($parameterInjection);
        }
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
        $this->parameterInjections[$parameterInjection->getParameterName()] = $parameterInjection;
    }

    /**
     * Merge another definition into the current definition
     *
     * In case of conflicts, the latter prevails (i.e. the other definition)
     *
     * @param MethodInjection $methodInjection
     */
    public function merge(MethodInjection $methodInjection)
    {
        // Merge parameter injections
        foreach ($methodInjection->getParameterInjections() as $parameterName => $parameterInjection) {
            if (array_key_exists($parameterName, $this->parameterInjections)) {
                // Merge
                $this->parameterInjections[$parameterName]->merge($parameterInjection);
            } else {
                // Add
                $this->parameterInjections[$parameterName] = $parameterInjection;
            }
        }
    }

}
