<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.com/PHP-DI/
 * @copyright 2012 Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Definition;

use DI\Annotations\Inject;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\PhpParser;
use Doctrine\Common\Annotations\Reader;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionProperty;

/**
 * Reads DI class definitions in annotations such as @ Inject and @ var annotations
 *
 * Uses Reflection, Doctrine's Annotations and regex docblock parsing
 */
class AnnotationDefinitionReader implements DefinitionReader
{

    /**
     * @var Reader
     */
    private $annotationReader;

    /**
     * @var PhpParser
     */
    private $phpParser;


    public function __construct()
    {
        $this->phpParser = new PhpParser();
    }

    /**
     * {@inheritdoc}
     * @throws AnnotationException
     * @throws \InvalidArgumentException The class doesn't exist
     */
    public function getDefinition($name)
    {
        if (!$this->classExists($name)) {
            return null;
        }
        $reflectionClass = new ReflectionClass($name);

        $classDefinition = new ClassDefinition($name);

        // Scope annotation
        $scopeAnnotation = $this->getAnnotationReader()->getClassAnnotation($reflectionClass, 'DI\Annotations\Scope');
        if ($scopeAnnotation !== null && $scopeAnnotation->value) {
            $classDefinition->setScope($scopeAnnotation->value);
        }

        // Browse the class properties looking for annotated properties
        $this->readProperties($reflectionClass, $classDefinition);

        // Look for annotated constructor
        $constructor = $reflectionClass->getConstructor();
        if ($constructor) {
            $this->readConstructor($reflectionClass, $constructor, $classDefinition);
        }

        // Browse the object's methods looking for annotated methods
        $this->readMethods($reflectionClass, $classDefinition);

        return $classDefinition;
    }

    /**
     * Browse the class properties looking for annotated properties
     * @param ReflectionClass $reflectionClass
     * @param ClassDefinition $classDefinition
     */
    private function readProperties(ReflectionClass $reflectionClass, ClassDefinition $classDefinition)
    {
        foreach ($reflectionClass->getProperties() as $property) {

            // Ignore static properties
            if ($property->isStatic()) {
                continue;
            }

            // Look for @Inject annotation
            $annotation = $this->getAnnotationReader()->getPropertyAnnotation($property, 'DI\Annotations\Inject');
            if ($annotation !== null) {
                /** @var $annotation Inject */

                $entryName = $annotation->getName();
                if ($entryName == null) {
                    // Look for @var content
                    $entryName = $this->getPropertyType($reflectionClass, $property);
                }
                $propertyInjection = new PropertyInjection($property->name, $entryName, $annotation->isLazy());
                $classDefinition->addPropertyInjection($propertyInjection);
            }

        }
    }

    /**
     * Browse the object's methods looking for annotated methods
     * @param ReflectionClass $reflectionClass
     * @param ClassDefinition $classDefinition
     */
    private function readConstructor(ReflectionClass $reflectionClass, ReflectionMethod $reflectionMethod, ClassDefinition $classDefinition)
    {
        // Look for @Inject annotation
        /** @var $annotation Inject|null */
        $annotation = $this->getAnnotationReader()->getMethodAnnotation($reflectionMethod, 'DI\Annotations\Inject');

        if ($annotation) {
            // @Inject found, create MethodInjection
            $methodInjection = new MethodInjection($reflectionMethod->name);
            $classDefinition->setConstructorInjection($methodInjection);

            // Read method parameters annotations
            $this->readMethodParameters($reflectionClass, $reflectionMethod, $annotation, $methodInjection);
        }
    }

    /**
     * Browse the object's methods looking for annotated methods
     * @param ReflectionClass $reflectionClass
     * @param ClassDefinition $classDefinition
     */
    private function readMethods(ReflectionClass $reflectionClass, ClassDefinition $classDefinition)
    {
        foreach ($reflectionClass->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            // Ignore constructor and static methods
            if ($method->isStatic() || $method->isConstructor()) {
                continue;
            }

            $this->readMethod($reflectionClass, $method, $classDefinition);
        }
    }

    /**
     * Browse the object's methods looking for annotated methods
     * @param ReflectionClass $reflectionClass
     * @param ClassDefinition $classDefinition
     */
    private function readMethod(ReflectionClass $reflectionClass, ReflectionMethod $reflectionMethod, ClassDefinition $classDefinition)
    {
        // Look for @Inject annotation
        /** @var $annotation Inject|null */
        $annotation = $this->getAnnotationReader()->getMethodAnnotation($reflectionMethod, 'DI\Annotations\Inject');

        if ($annotation) {
            // @Inject found, create MethodInjection
            $methodInjection = new MethodInjection($reflectionMethod->name);
            $classDefinition->addMethodInjection($methodInjection);

            // Read method parameters annotations
            $this->readMethodParameters($reflectionClass, $reflectionMethod, $annotation, $methodInjection);
        }
    }

    /**
     * @param ReflectionClass  $reflectionClass
     * @param ReflectionMethod $method
     * @param Inject           $annotation
     * @param MethodInjection  $methodInjection
     */
    private function readMethodParameters(
        ReflectionClass $reflectionClass,
        ReflectionMethod $method,
        Inject $annotation,
        MethodInjection $methodInjection
    ) {
        $annotationParameters = $annotation->getParameters();

        // For each param
        $index = 0;
        foreach ($method->getParameters() as $parameter) {

            $entryName = null;

            $annotationParameter = null;
            // @Inject has definition for this parameter (not named)
            if (isset($annotationParameters[$index])) {
                $annotationParameter = $annotationParameters[$index];
            }
            // @Inject has definition for this parameter (named)
            if (isset($annotationParameters[$parameter->name])) {
                $annotationParameter = $annotationParameters[$parameter->name];
            }
            if (isset($annotationParameter['name'])) {
                $entryName = $annotationParameter['name'];
            }

            // Look for @param tag
            if ($entryName === null) {
                $entryName = $this->getParameterType($reflectionClass, $method, $parameter);
            }

            $methodInjection->addParameterInjection(new ParameterInjection($parameter->name, $entryName));

            $index++;
        }
    }

    /**
     * @return Reader The annotation reader
     */
    public function getAnnotationReader()
    {
        if ($this->annotationReader == null) {
            AnnotationRegistry::registerAutoloadNamespace('DI\Annotations', __DIR__ . '/../../');
            $this->annotationReader = new AnnotationReader();
        }
        return $this->annotationReader;
    }

    /**
     * @param Reader $annotationReader The annotation reader
     */
    public function setAnnotationReader(Reader $annotationReader)
    {
        $this->annotationReader = $annotationReader;
    }

    /**
     * Parse the docblock of the property to get the var annotation
     * @param ReflectionClass    $class
     * @param ReflectionProperty $property
     * @throws AnnotationException
     * @return string|null Type of the property (content of var annotation)
     */
    private function getPropertyType(ReflectionClass $class, ReflectionProperty $property)
    {
        // Get the content of the @var annotation
        if (preg_match('/@var\s+([^\s]+)/', $property->getDocComment(), $matches)) {
            list(, $type) = $matches;
        } else {
            return null;
        }

        // If the class name is not fully qualified (i.e. doesn't start with a \)
        if ($type[0] !== '\\') {
            $alias = (false === $pos = strpos($type, '\\')) ? $type : substr($type, 0, $pos);
            $loweredAlias = strtolower($alias);

            // Retrieve "use" statements
            $uses = $this->phpParser->parseClass($property->getDeclaringClass());

            $found = false;

            if ($this->classExists($class->getNamespaceName() . '\\' . $type)) {
                $type = $class->getNamespaceName() . '\\' . $type;
                $found = true;
            } elseif (isset($uses[$loweredAlias])) {
                // Imported classes
                if (false !== $pos) {
                    $type = $uses[$loweredAlias] . substr($type, $pos);
                } else {
                    $type = $uses[$loweredAlias];
                }
                $found = true;
            } elseif (isset($uses['__NAMESPACE__']) && $this->classExists($uses['__NAMESPACE__'] . '\\' . $type)) {
                // Class namespace
                $type = $uses['__NAMESPACE__'] . '\\' . $type;
                $found = true;
            } elseif ($this->classExists($type)) {
                // No namespace
                $found = true;
            }

            if (!$found) {
                throw new AnnotationException("The @var annotation on {$class->name}::" . $property->getName()
                    . " contains a non existent class. Did you maybe forget to add a 'use' statement for this annotation?");
            }
        }

        if (!$this->classExists($type)) {
            throw new AnnotationException("The @var annotation on {$class->name}::" . $property->getName()
                . " contains a non existent class");
        }

        // Remove the leading \ (FQN shouldn't contain it)
        $type = ltrim($type, '\\');

        return $type;
    }

    /**
     * Parse the docblock of the property to get the param annotation
     * @param ReflectionClass  $class
     * @param ReflectionMethod $method
     * @throws AnnotationException
     * @return string|null Type of the property (content of var annotation)
     */
    private function getParameterType(ReflectionClass $class, ReflectionMethod $method, ReflectionParameter $parameter)
    {
        // Use reflection
        $parameterClass = $parameter->getClass();
        if ($parameterClass !== null) {
            return $parameterClass->name;
        }

        $parameterName = $parameter->name;
        // Get the content of the @param annotation
        if (preg_match('/@param\s+([^\s]+)\s+\$' . $parameterName . '/', $method->getDocComment(), $matches)) {
            list(, $type) = $matches;
        } else {
            return null;
        }

        // If the class name is not fully qualified (i.e. doesn't start with a \)
        if ($type[0] !== '\\') {
            $alias = (false === $pos = strpos($type, '\\')) ? $type : substr($type, 0, $pos);
            $loweredAlias = strtolower($alias);

            // Retrieve "use" statements
            $uses = $this->phpParser->parseClass($method->getDeclaringClass());

            $found = false;

            if ($this->classExists($class->getNamespaceName() . '\\' . $type)) {
                $type = $class->getNamespaceName() . '\\' . $type;
                $found = true;
            } elseif (isset($uses[$loweredAlias])) {
                // Imported classes
                if (false !== $pos) {
                    $type = $uses[$loweredAlias] . substr($type, $pos);
                } else {
                    $type = $uses[$loweredAlias];
                }
                $found = true;
            } elseif (isset($uses['__NAMESPACE__']) && $this->classExists($uses['__NAMESPACE__'] . '\\' . $type)) {
                // Class namespace
                $type = $uses['__NAMESPACE__'] . '\\' . $type;
                $found = true;
            } elseif ($this->classExists($type)) {
                // No namespace
                $found = true;
            }

            if (!$found) {
                throw new AnnotationException("The @param annotation for parameter $parameterName of {$class->name}::" . $method->name
                    . " contains a non existent class. Did you maybe forget to add a 'use' statement for this annotation?");
            }
        }

        if (!$this->classExists($type)) {
            throw new AnnotationException("The @param annotation for parameter $parameterName of {$class->name}::" . $method->name
                . " contains a non existent class");
        }

        // Remove the leading \ (FQN shouldn't contain it)
        $type = ltrim($type, '\\');

        return $type;
    }

    /**
     * @param string $class
     * @return bool
     */
    private function classExists($class)
    {
        return class_exists($class) || interface_exists($class);
    }

}
