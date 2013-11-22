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
 * Definition of a value or class using a callable.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class CallableDefinition implements Definition
{
    /**
     * Entry name.
     * @var string
     */
    private $name;

    /**
     * Callable that returns the value.
     * @var callable
     */
    private $callable;

    /**
     * @param string   $name     Entry name
     * @param callable $callable Callable that returns the value associated to the entry name.
     */
    public function __construct($name, $callable)
    {
        $this->name = $name;
        $this->callable = $callable;
    }

    /**
     * @return string Entry name.
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return callable
     */
    public function getCallable()
    {
        return $this->callable;
    }

    /**
     * {@inheritdoc}
     */
    public function merge(Definition $definition)
    {
        throw new \BadMethodCallException("Impossible to merge a CallableDefinition with another definition");
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
