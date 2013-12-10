<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Definition\Source;

use DI\Definition\ClassInjection\MethodInjection;
use DI\Definition\ClassInjection\PropertyInjection;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;

/**
 * Source of definitions for instantiating classes.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 * @since 4.0
 */
interface ClassDefinitionSource
{
    /**
     * Returns the injection definition for the given property.
     *
     * @param string             $entryName
     * @param ReflectionProperty $property
     *
     * @return PropertyInjection|null
     */
    public function getPropertyInjection($entryName, ReflectionProperty $property);

    /**
     * Returns the injection definition for the given method.
     *
     * @param string           $entryName
     * @param ReflectionMethod $method
     *
     * @return MethodInjection|null
     */
    public function getMethodInjection($entryName, ReflectionMethod $method);
}
