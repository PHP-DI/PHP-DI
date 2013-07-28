<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.io/PHP-DI/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI;

use DI\Definition\ClassDefinition;

/**
 * Factory class, responsible of instantiating classes
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
interface FactoryInterface
{

    /**
     * Create a new instance of the class
     *
     * @param ClassDefinition $classDefinition
     *
     * @return object The instance
     */
    function createInstance(ClassDefinition $classDefinition);

    /**
     * @param ClassDefinition $classDefinition
     * @param object          $instance
     *
     * @return object The instance
     */
    function injectInstance(ClassDefinition $classDefinition, $instance);

}
