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
 * Defines a reference to an environment variable, with fallback to a default
 * value if the environment variable is not defined.
 *
 * @author James Harris <james.harris@icecave.com.au>
 */
class EnvironmentVariableDefinition implements CacheableDefinition
{
    /**
     * Entry name
     * @var string
     */
    private $name;

    /**
     * The name of the environment variable
     * @var string
     */
    private $variableName;

    /**
     * Whether or not the environment variable definition is optional
     *
     * If true and the environment variable given by $variableName has not been
     * defined, $defaultValue is used.
     *
     * @var boolean
     */
    private $isOptional;

    /**
     * The default value to use if the environment variable is optional and not provided
     * @var mixed
     */
    private $defaultValue;

    /**
     * @var string|null
     */
    private $scope;

    /**
     * @param string $name Entry name
     * @param string $variableName The name of the environment variable
     * @param boolean $isOptional Whether or not the environment variable definition is optional
     * @param mixed $defaultValue The default value to use if the environment variable is optional and not provided
     */
    public function __construct($name, $variableName, $isOptional = false, $defaultValue = null)
    {
        $this->name = $name;
        $this->variableName = $variableName;
        $this->isOptional = $isOptional;
        $this->defaultValue = $defaultValue;
    }

    /**
     * @return string Entry name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string The name of the environment variable
     */
    public function getVariableName()
    {
        return $this->variableName;
    }

    /**
     * @return boolean Whether or not the environment variable definition is optional
     */
    public function isOptional()
    {
        return $this->isOptional;
    }

    /**
     * @return mixed The default value to use if the environment variable is optional and not provided
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    /**
     * @param string $scope
     */
    public function setScope($scope)
    {
        $this->scope = $scope;
    }

    /**
     * {@inheritdoc}
     */
    public function getScope()
    {
        return $this->scope ?: Scope::SINGLETON;
    }
}
