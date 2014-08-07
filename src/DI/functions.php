<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI;

use DI\Definition\EntryReference;
use DI\Definition\Helper\FactoryDefinitionHelper;
use DI\Definition\Helper\ClassDefinitionHelper;
use DI\Definition\Helper\EnvironmentVariableDefinitionHelper;

/**
 * Helper for defining an object.
 *
 * @param string|null $className Class name of the object.
 *                               If null, the name of the entry (in the container) will be used as class name.
 *
 * @return ClassDefinitionHelper
 */
function object($className = null)
{
    return new ClassDefinitionHelper($className);
}

/**
 * Helper for defining a container entry using a factory function/callable.
 *
 * @param callable $factory The factory is a callable that takes the container as parameter
 *                          and returns the value to register in the container.
 *
 * @return FactoryDefinitionHelper
 */
function factory($factory)
{
    return new FactoryDefinitionHelper($factory);
}

/**
 * Helper for referencing another container entry in an object definition.
 *
 * @param string $entryName
 *
 * @return EntryReference
 */
function link($entryName)
{
    return new EntryReference($entryName);
}

/**
 * Helper for referencing environment variables.
 *
 * @param string $variableName The name of the environment variable.
 * @param mixed $defaultValue The default value to be used if the environment variable is not defined.
 *
 * @return EnvironmentVariableDefinitionHelper
 */
function env($variableName, $defaultValue = null)
{
    // Only mark as optional if the default value was *explicitly* provided.
    $isOptional = 2 === func_num_args();

    return new EnvironmentVariableDefinitionHelper($variableName, $isOptional, $defaultValue);
}
