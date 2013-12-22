<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.io/PHP-DI/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Definition;

use DI\Scope;

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
     * @var Scope
     */
    private $scope;

    /**
     * Callable that returns the value.
     * @var callable
     */
    private $callable;

    /**
     * @param string     $name     Entry name
     * @param callable   $callable Callable that returns the value associated to the entry name.
     * @param Scope|null $scope
     */
    public function __construct($name, $callable, Scope $scope = null)
    {
        $this->name = $name;
        $this->callable = $callable;
        $this->scope = $scope;
    }

    /**
     * @return string Entry name.
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Default scope is singleton: the callable is called once and the result is shared.
     *
     * {@inheritdoc}
     */
    public function getScope()
    {
        return $this->scope ?: Scope::SINGLETON();
    }

    /**
     * @return callable
     */
    public function getCallable()
    {
        return $this->callable;
    }
}
