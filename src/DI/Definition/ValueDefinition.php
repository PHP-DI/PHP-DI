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
 * Definition of a value for dependency injection
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class ValueDefinition implements Definition
{

    /**
     * Entry name
     * @var string
     */
    private $name;

    /**
     * @var mixed
     */
    private $value;

    /**
     * @param string $name Entry name
     * @param mixed $value
     */
    public function __construct($name, $value)
    {
        $this->name = $name;
        $this->value = $value;
    }

    /**
     * @return string Entry name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * A value definition is like a constant, there is nothing to compute, the value is the same for everyone.
     *
     * {@inheritdoc}
     */
    public function getScope()
    {
        return Scope::SINGLETON();
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * {@inheritdoc}
     */
    public function merge(Definition $definition)
    {
        throw new \BadMethodCallException("Impossible to merge a ValueDefinition with another definition");
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
