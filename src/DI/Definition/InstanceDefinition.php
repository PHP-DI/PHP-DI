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
 * Defines injections on an existing class instance.
 *
 * @since  5.0
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class InstanceDefinition implements Definition
{
    /**
     * Instance on which to inject dependencies.
     *
     * @var object
     */
    private $instance;

    /**
     * @var ClassDefinition
     */
    private $classDefinition;

    /**
     * @param object          $instance
     * @param ClassDefinition $classDefinition
     */
    public function __construct($instance, ClassDefinition $classDefinition)
    {
        $this->instance = $instance;
        $this->classDefinition = $classDefinition;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        // Name are superfluous for instance definitions
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function getScope()
    {
        return Scope::PROTOTYPE();
    }

    /**
     * @return object
     */
    public function getInstance()
    {
        return $this->instance;
    }

    /**
     * @return ClassDefinition
     */
    public function getClassDefinition()
    {
        return $this->classDefinition;
    }
}
