<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Definition;

use DI\Scope;

/**
 * Definition of a value or class with a factory.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class FactoryDefinition implements Definition
{
    /**
     * Entry name.
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $scope;

    /**
     * Callable that returns the value.
     * @var callable
     */
    private $factory;

    /**
     * @param string      $name    Entry name
     * @param callable    $factory Callable that returns the value associated to the entry name.
     * @param string|null $scope
     */
    public function __construct($name, $factory, $scope = null)
    {
        $this->name = $name;
        $this->factory = $factory;
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
        return $this->scope ?: Scope::SINGLETON;
    }

    /**
     * @return callable Callable that returns the value associated to the entry name.
     */
    public function getCallable()
    {
        return $this->factory;
    }
}
