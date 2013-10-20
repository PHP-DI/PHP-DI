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
 * Component responsible of creating and injecting dependencies as defined in Definitions.
 *
 * @since 4.0
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
interface Injector
{
    /**
     * Create a new instance of a class and injects all dependencies.
     *
     * @param ClassDefinition $classDefinition
     *
     * @return object The instance
     */
    public function createInstance(ClassDefinition $classDefinition);

    /**
     * Injects dependencies on an existing instance.
     *
     * @param ClassDefinition $classDefinition
     * @param object          $instance
     *
     * @return object The instance
     */
    public function injectOnInstance(ClassDefinition $classDefinition, $instance);
}
