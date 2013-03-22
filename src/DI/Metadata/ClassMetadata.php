<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.com/PHP-DI/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Metadata;

use DI\Scope;

/**
 * Class metadata for configuring dependency injection
 */
class ClassMetadata
{

    /**
     * Property injections indexed by the property name
     * @var array
     */
    private $propertyInjections = array();

    /**
     * Method injections indexed by the method name
     * @var array
     */
    private $methodInjections = array();

    /**
     * @var Scope
     */
    private $scope;

    /**
     * @var bool
     */
    private $lazy = false;

    public function __construct()
    {
        // Default scope
        $this->scope = Scope::SINGLETON();
    }

    /**
     * @return string[] Property injections indexed by the property name
     */
    public function getPropertyInjections()
    {
        return $this->propertyInjections;
    }

    /**
     * @param string $propertyName
     * @param string $beanName
     */
    public function setPropertyInjection($propertyName, $beanName)
    {
        $this->propertyInjections[$propertyName] = $beanName;
    }

    /**
     * @return string[] Method injections indexed by the method name
     */
    public function getMethodInjections()
    {
        return $this->methodInjections;
    }

    /**
     * @param string   $methodName
     * @param string[] $parameters
     */
    public function setMethodInjection($methodName, array $parameters)
    {
        $this->methodInjections[$methodName] = $parameters;
    }

    /**
     * @param Scope $scope
     */
    public function setScope(Scope $scope)
    {
        $this->scope = $scope;
    }

    /**
     * @return Scope
     */
    public function getScope()
    {
        return $this->scope;
    }

    /**
     * @return bool
     */
    public function getLazy()
    {
        return $this->lazy;
    }

    /**
     * @param bool $lazy
     */
    public function setLazy($lazy)
    {
        $this->lazy = (bool) $lazy;
    }

}
