<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Definition\ObjectDefinition;

use DI\Definition\AbstractFunctionCallDefinition;

/**
 * Describe an injection in an object method.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class MethodInjection extends AbstractFunctionCallDefinition
{
    /**
     * @var string
     */
    private $methodName;

    /**
     * @param string $methodName
     * @param array  $parameters
     */
    public function __construct($methodName, array $parameters = array())
    {
        $this->methodName = (string) $methodName;
        $this->parameters = $parameters;
    }

    public static function constructor(array $parameters = array())
    {
        return new self('__construct', $parameters);
    }

    /**
     * @return string Method name
     */
    public function getMethodName()
    {
        return $this->methodName;
    }

    public function merge(MethodInjection $definition)
    {
        // In case of conflicts, the current definition prevails.
        $this->parameters = $this->parameters + $definition->parameters;
    }
}
