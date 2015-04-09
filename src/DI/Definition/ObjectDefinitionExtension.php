<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Definition;

use DI\Definition\Exception\DefinitionException;

/**
 * Object definition that extends another definition.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class ObjectDefinitionExtension extends ClassDefinition implements HasSubDefinition
{
    /**
     * {@inheritdoc}
     */
    public function getSubDefinitionName()
    {
        return $this->getClassName();
    }

    /**
     * {@inheritdoc}
     */
    public function setSubDefinition(Definition $definition)
    {
        if (! $definition instanceof ClassDefinition) {
            throw new DefinitionException(sprintf(
                "Container entry '%s' extends entry '%s' which is not an object",
                $this->getName(),
                $definition->getName()
            ));
        }

        // The current prevails
        if ($this->className === null) {
            $this->className = $definition->className;
        }
        if ($this->scope === null) {
            $this->scope = $definition->scope;
        }
        if ($this->lazy === null) {
            $this->lazy = $definition->lazy;
        }

        // Merge constructor injection
        if ($definition->getConstructorInjection() !== null) {
            if ($this->constructorInjection !== null) {
                // Merge
                $this->constructorInjection->merge($definition->getConstructorInjection());
            } else {
                // Set
                $this->constructorInjection = $definition->getConstructorInjection();
            }
        }

        // Merge property injections
        foreach ($definition->getPropertyInjections() as $propertyName => $propertyInjection) {
            if (! array_key_exists($propertyName, $this->propertyInjections)) {
                // Add
                $this->propertyInjections[$propertyName] = $propertyInjection;
            }
        }

        // Merge method injections
        foreach ($definition->getMethodInjections() as $methodName => $methodInjection) {
            if (array_key_exists($methodName, $this->methodInjections)) {
                // Merge
                $this->methodInjections[$methodName]->merge($methodInjection);
            } else {
                // Add
                $this->methodInjections[$methodName] = $methodInjection;
            }
        }
    }
}
