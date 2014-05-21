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
 * Describe the call to an object's constructor.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class ConstructorInjection extends AbstractFunctionCallDefinition
{
    /**
     * @var string
     */
    private $className;

    /**
     * @param string $className
     * @param array  $parameters
     */
    public function __construct($className, array $parameters = array())
    {
        $this->className = (string) $className;
        $this->parameters = $parameters;
    }

    /**
     * @param string $className
     */
    public function setClassName($className)
    {
        $this->className = $className;
    }

    /**
     * {@inheritdoc}
     */
    public function getReflection()
    {
        $class = new \ReflectionClass($this->className);
        return $class->getConstructor();
    }

    /**
     * {@inheritdoc}
     */
    public function getCallable()
    {
        $class = new \ReflectionClass($this->className);

        return function () use ($class) {
            $class->newInstanceArgs(func_get_args());
        };
    }
}
