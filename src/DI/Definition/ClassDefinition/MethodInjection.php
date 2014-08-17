<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Definition\ClassDefinition;

use DI\Definition\AbstractFunctionCallDefinition;

/**
 * Describe an injection in a class method.
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
}
