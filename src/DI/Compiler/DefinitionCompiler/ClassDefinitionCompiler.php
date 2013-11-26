<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Compiler\DefinitionCompiler;

use DI\Definition\ClassDefinition;
use DI\Definition\ClassInjection\MethodInjection;
use DI\Definition\ClassInjection\PropertyInjection;
use DI\Definition\ClassInjection\UndefinedInjection;
use DI\Definition\Definition;
use DI\Definition\EntryReference;
use DI\Definition\Exception\DefinitionException;
use DI\DependencyException;
use DI\Scope;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionParameter;

/**
 * Compiles a ClassDefinition to PHP code.
 *
 * @since 4.0
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class ClassDefinitionCompiler implements DefinitionCompiler
{
    /**
     * {@inheritdoc}
     */
    public function compile(Definition $definition)
    {
        if (! $definition instanceof ClassDefinition) {
            throw new \InvalidArgumentException(sprintf(
                'This definition compiler is only compatible with ClassDefinition objects, %s given',
                get_class($definition)
            ));
        }

        $classReflection = new ReflectionClass($definition->getClassName());

        if (!$classReflection->isInstantiable()) {
            throw new DependencyException("$classReflection->name is not instantiable");
        }

        // Constructor
        $code = $this->compileConstructor($classReflection, $definition->getConstructorInjection());

        // Properties
        foreach ($definition->getPropertyInjections() as $propertyInjection) {
            $code .= PHP_EOL . $this->compileProperty($classReflection, $propertyInjection);
        }

        // Methods
        foreach ($definition->getMethodInjections() as $methodInjection) {
            $code .= PHP_EOL . $this->compileMethod($classReflection, $methodInjection);
        }

        // Lazy -> proxy
        if ($definition->isLazy()) {
            return $this->compileProxy($definition, $code);
        }

        // Singleton
        if ($definition->getScope() == Scope::SINGLETON()) {
            return $code . PHP_EOL . 'return new \DI\Compiler\SharedEntry($object);';
        }

        return $code . PHP_EOL . 'return $object;';
    }

    private function compileConstructor(ReflectionClass $classReflection, MethodInjection $constructorInjection = null)
    {
        $args = $this->compileMethodParameters($constructorInjection, $classReflection->getConstructor());

        if ($args) {
            $args = PHP_EOL . $this->indent($args) . PHP_EOL;
        }

        return sprintf('$object = new \%s(%s);', $classReflection->getName(), $args);
    }

    private function compileProperty(ReflectionClass $classReflection, PropertyInjection $propertyInjection)
    {
        $propertyName = $propertyInjection->getPropertyName();
        $property = $classReflection->getProperty($propertyName);

        $value = $propertyInjection->getValue();

        if ($value instanceof UndefinedInjection) {
            throw new DefinitionException(sprintf(
                "The property %s::%s has no value defined or guessable",
                $classReflection->getName(),
                $propertyInjection->getPropertyName()
            ));
        }

        if ($value instanceof EntryReference) {
            $valueToSet = sprintf('$this->get(%s)', var_export($value->getName(), true));
        } else {
            $valueToSet = var_export($value, true);
        }

        if ($property->isPublic()) {
            return sprintf('$object->%s = %s;', $propertyName, $valueToSet);
        }

        // The property is not public, we need to use reflection
        $className = $classReflection->getName();
        return <<<PHP
\$property = new ReflectionProperty('$className', '$propertyName');
\$property->setAccessible(true);
\$property->setValue(\$object, $valueToSet);
PHP;
    }

    private function compileMethod(ReflectionClass $classReflection, MethodInjection $methodInjection)
    {
        $methodReflection = $classReflection->getMethod($methodInjection->getMethodName());

        $args = $this->compileMethodParameters($methodInjection, $methodReflection);

        if ($args) {
            $args = PHP_EOL . $this->indent($args) . PHP_EOL;
        }

        return sprintf('$object->%s(%s);', $methodInjection->getMethodName(), $args);
    }

    private function compileMethodParameters(
        MethodInjection $methodInjection = null,
        ReflectionMethod $methodReflection = null
    ) {
        if (!$methodReflection) {
            return '';
        }

        // Check the number of parameters match
        $nbRequiredParameters = $methodReflection->getNumberOfRequiredParameters();
        $parameterInjections = $methodInjection ? $methodInjection->getParameters() : array();
        if (count($parameterInjections) < $nbRequiredParameters) {
            throw new DefinitionException(sprintf(
                "%s::%s takes %d parameters, %d defined or guessed",
                $methodReflection->getDeclaringClass()->getName(),
                $methodReflection->getName(),
                $nbRequiredParameters,
                count($parameterInjections)
            ));
        }

        // No parameters
        if (empty($parameterInjections)) {
            return '';
        }

        $reflectionParameters = $methodReflection->getParameters();

        $args = array();
        foreach ($parameterInjections as $index => $value) {
            // If the parameter is optional and wasn't specified, we take its default value
            if ($value instanceof UndefinedInjection) {
                if ($reflectionParameters[$index]->isOptional()) {
                    $value = $this->getParameterDefaultValue($reflectionParameters[$index], $methodReflection);
                } else {
                    throw new DefinitionException(sprintf(
                        "The parameter '%s' of %s::%s has no value defined or guessable",
                        $reflectionParameters[$index]->getName(),
                        $methodReflection->getDeclaringClass()->getName(),
                        $methodReflection->getName()
                    ));
                }
            }

            if ($value instanceof EntryReference) {
                $args[] = sprintf('$this->get(%s)', var_export($value->getName(), true));
            } else {
                $args[] = var_export($value, true);
            }
        }

        return implode(',' . PHP_EOL, $args);
    }

    /**
     * Returns the default value of a function parameter.
     *
     * @param ReflectionParameter $reflectionParameter
     * @param ReflectionMethod    $reflectionMethod
     *
     * @throws DefinitionException Can't get default values from PHP internal classes and methods
     * @return mixed
     */
    private function getParameterDefaultValue(
        ReflectionParameter $reflectionParameter,
        ReflectionMethod $reflectionMethod
    ) {
        try {
            return $reflectionParameter->getDefaultValue();
        } catch (ReflectionException $e) {
            throw new DefinitionException(sprintf(
                "The parameter '%s' of %s::%s has no type defined or guessable. It has a default value, "
                . "but the default value can't be read through Reflection because it is a PHP internal class.",
                $reflectionParameter->getName(),
                $reflectionMethod->getDeclaringClass()->getName(),
                $reflectionMethod->getName()
            ));
        }
    }

    /**
     * Compile a class definition in a proxy.
     *
     * @param ClassDefinition $definition
     * @param string          $code
     *
     * @return string
     */
    private function compileProxy(ClassDefinition $definition, $code)
    {
        $code = $this->indent($code);
        $className = $definition->getClassName();

        return <<<PHP
\$resolver = function (& \$wrappedObject, \$proxy, \$method, \$parameters, & \$initializer) {
$code
    \$wrappedObject = \$object;
    \$initializer = null;
    return true;
};
return \$this->createProxy('$className', \$resolver);
PHP;
    }

    /**
     * Indent each line.
     *
     * @param string $str
     * @return string
     */
    private function indent($str)
    {
        $lines = explode(PHP_EOL, $str);

        $lines = array_map(function ($line) {
            return '    ' . $line;
        }, $lines);

        return implode(PHP_EOL, $lines);
    }
}
