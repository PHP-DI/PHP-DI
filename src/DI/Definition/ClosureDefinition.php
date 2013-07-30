<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.io/PHP-DI/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Definition;

use Closure;
use DI\Container;
use DI\Definition\Exception\DefinitionException;

/**
 * Definition of a value or class using a closure
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class ClosureDefinition implements Definition
{

    /**
     * Entry name
     * @var string
     */
    private $name;

    /**
     * Anonymous function
     * @var Closure
     */
    private $closure;

    /**
     * @param string $name Entry name
     * @param Closure $closure Anonymous function
     */
    public function __construct($name, Closure $closure)
    {
        $this->name = $name;
        $this->closure = $closure;
    }

    /**
     * @return string Entry name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return Closure
     */
    public function getClosure()
    {
        return $this->closure;
    }

    /**
     * Returns the result of the anonymous function
     * @param Container $container
     * @return mixed
     */
    public function getValue(Container $container)
    {
        $closure = $this->closure;
        return $closure($container);
    }

    /**
     * {@inheritdoc}
     */
    public function merge(Definition $definition)
    {
        throw new \BadMethodCallException("Impossible to merge a ClosureDefinition with another definition");
    }

    /**
     * {@inheritdoc}
     */
    public function isCacheable()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public static function isMergeable()
    {
        return false;
    }

}
