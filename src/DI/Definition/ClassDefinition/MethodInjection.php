<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.io/PHP-DI/
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
    private $className;

    /**
     * @var string
     */
    private $methodName;

    /**
     * @var object
     */
    private $object;

    /**
     * @param string $className
     * @param string $methodName
     * @param array  $parameters
     */
    public function __construct($className, $methodName, array $parameters = array())
    {
        $this->className = (string) $className;
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
     * {@inheritdoc}
     */
    public function getReflection()
    {
        return new \ReflectionMethod($this->object, $this->methodName);
    }

    /**
     * {@inheritdoc}
     */
    public function getCallable()
    {
        if (! $this->object) {
            throw new \RuntimeException('No object was set in MethodInjection');
        }

        return array($this->object, $this->methodName);
    }

    /**
     * @param object $object
     */
    public function setObject($object)
    {
        $this->object = $object;
    }
}
