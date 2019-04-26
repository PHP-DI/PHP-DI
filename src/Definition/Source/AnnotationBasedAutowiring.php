<?php

declare(strict_types=1);

namespace DI\Definition\Source;

use DI\Annotation\Inject;
use DI\Annotation\Injectable;
use DI\Definition\AutowireDefinition;
use DI\Definition\Exception\InvalidAnnotation;
use DI\Definition\ObjectDefinition;
use DI\Definition\ObjectDefinition\MethodInjection;
use DI\Definition\ObjectDefinition\PropertyInjection;
use DI\Definition\Reference;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Annotations\SimpleAnnotationReader;
use InvalidArgumentException;
use PhpDocReader\PhpDocReader;
use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionProperty;
use UnexpectedValueException;

/**
 * Provides DI definitions by reading annotations such as @ Inject and @ var annotations.
 *
 * Uses Autowiring, Doctrine's Annotations and regex docblock parsing.
 * This source automatically includes the reflection source.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class AnnotationBasedAutowiring implements DefinitionSource, Autowiring
{
    // Annotations configuration flags:
    // enable on implicit definitions
    const IMPLICIT = 1;
    // enable on all autowire definitions (which are written in DI config) by default
    const EXPLICIT = 2;
    // read @Injectable annotations for classes
    const INJECTABLE = 4;
    // read @Inject annotations for properties
    const PROPERTIES = 8;
    // read @Inject annotations for methods' parameters
    const METHODS = 16;
    // all options enabled
    const ALL = 31;

    /**
     * @var int
     */
    private $flags;

    /**
     * @var Reader
     */
    private $annotationReader;

    /**
     * @var PhpDocReader
     */
    private $phpDocReader;

    /**
     * @var bool
     */
    private $ignorePhpDocErrors;

    public function __construct($ignorePhpDocErrors = false, int $flags = 0)
    {
        $this->ignorePhpDocErrors = (bool) $ignorePhpDocErrors;
        $this->flags = $flags > 0 ? $flags : self::ALL; // all flags turned on by default
    }

    public function autowire(string $name, ObjectDefinition $definition = null)
    {
        $className = $definition ? $definition->getClassName() : $name;

        if (!class_exists($className) && !interface_exists($className)) {
            return $definition;
        }

        $definition = $definition ?: new ObjectDefinition($name);
        $useAnnotations = $definition instanceof AutowireDefinition
            ? ($definition->isUsingAnnotations() ?? ($this->flags & self::EXPLICIT))
            : ($this->flags & self::IMPLICIT);

        $class = null;
        if ($useAnnotations && $this->flags >= self::INJECTABLE) {
            $class = new ReflectionClass($className);

            if ($this->flags & self::INJECTABLE) {
                $this->readInjectableAnnotation($class, $definition);
            }

            // Browse the class properties looking for annotated properties
            if ($this->flags & self::PROPERTIES) {
                $this->readProperties($class, $definition);
            }

            // Browse the object's methods looking for annotated methods
            if ($this->flags & self::METHODS) {
                $this->readMethods($class, $definition);
            }
        }

        // constructor parameters should always be read, even if annotations are disabled (completely or i.a. for methods)
        // so that it behaves at least as ReflectionBasedAutowiring
        if (!$useAnnotations || !($this->flags & self::METHODS)) {
            $class = $class ?? new ReflectionClass($className);
            $this->readConstructor($class, $definition);
        }

        return $definition;
    }

    /**
     * {@inheritdoc}
     * @throws InvalidAnnotation
     * @throws InvalidArgumentException The class doesn't exist
     */
    public function getDefinition(string $name)
    {
        return $this->autowire($name);
    }

    /**
     * Autowiring cannot guess all existing definitions.
     */
    public function getDefinitions() : array
    {
        return [];
    }

    /**
     * Browse the class properties looking for annotated properties.
     */
    private function readProperties(ReflectionClass $class, ObjectDefinition $definition)
    {
        foreach ($class->getProperties() as $property) {
            if ($property->isStatic()) {
                continue;
            }
            $this->readProperty($property, $definition);
        }

        // Read also the *private* properties of the parent classes
        /** @noinspection PhpAssignmentInConditionInspection */
        while ($class = $class->getParentClass()) {
            foreach ($class->getProperties(ReflectionProperty::IS_PRIVATE) as $property) {
                if ($property->isStatic()) {
                    continue;
                }
                $this->readProperty($property, $definition, $class->getName());
            }
        }
    }

    private function readProperty(ReflectionProperty $property, ObjectDefinition $definition, $classname = null)
    {
        // Look for @Inject annotation
        $annotation = $this->getAnnotationReader()->getPropertyAnnotation($property, 'DI\Annotation\Inject');
        if (!$annotation instanceof Inject) {
            return;
        }

        // @Inject("name") or look for @var content
        $entryName = $annotation->getName() ?: $this->getPhpDocReader()->getPropertyClass($property);

        if ($entryName === null) {
            throw new InvalidAnnotation(sprintf(
                '@Inject found on property %s::%s but unable to guess what to inject, use a @var annotation',
                $property->getDeclaringClass()->getName(),
                $property->getName()
            ));
        }

        $definition->addPropertyInjection(
            new PropertyInjection($property->getName(), new Reference($entryName), $classname)
        );
    }

    /**
     * Browse the object's methods looking for annotated methods.
     */
    private function readMethods(ReflectionClass $class, ObjectDefinition $objectDefinition)
    {
        // This will look in all the methods, including those of the parent classes
        foreach ($class->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            if ($method->isStatic()) {
                continue;
            }

            $methodInjection = $this->getMethodInjection($method);

            if (! $methodInjection) {
                continue;
            }

            if ($method->isConstructor()) {
                $objectDefinition->completeConstructorInjection($methodInjection);
            } else {
                $objectDefinition->completeFirstMethodInjection($methodInjection);
            }
        }
    }

    /**
     * Browse the object's constructor parameters and inject dependencies.
     */
    private function readConstructor(ReflectionClass $class, ObjectDefinition $definition)
    {
        if (!($constructor = $class->getConstructor()) || !$constructor->isPublic()) {
            return;
        }

        $parameters = [];
        foreach ($constructor->getParameters() as $index => $parameter) {
            $entryName = $this->getMethodParameter($index, $parameter, []);

            if ($entryName !== null) {
                $parameters[$index] = new Reference($entryName);
            }
        }

        $constructorInjection = MethodInjection::constructor($parameters);
        $definition->completeConstructorInjection($constructorInjection);
    }

    /**
     * @param  ReflectionMethod $method
     *
     * @return MethodInjection|null
     */
    private function getMethodInjection(ReflectionMethod $method)
    {
        // Look for @Inject annotation
        try {
            $annotation = $this->getAnnotationReader()->getMethodAnnotation($method, 'DI\Annotation\Inject');
        } catch (InvalidAnnotation $e) {
            throw new InvalidAnnotation(sprintf(
                '@Inject annotation on %s::%s is malformed. %s',
                $method->getDeclaringClass()->getName(),
                $method->getName(),
                $e->getMessage()
            ), 0, $e);
        }

        // @Inject on constructor is implicit
        if (! ($annotation || $method->isConstructor())) {
            return null;
        }

        $annotationParameters = $annotation instanceof Inject ? $annotation->getParameters() : [];

        $parameters = [];
        foreach ($method->getParameters() as $index => $parameter) {
            $entryName = $this->getMethodParameter($index, $parameter, $annotationParameters);

            if ($entryName !== null) {
                $parameters[$index] = new Reference($entryName);
            }
        }

        if ($method->isConstructor()) {
            return MethodInjection::constructor($parameters);
        }

        return new MethodInjection($method->getName(), $parameters);
    }

    /**
     * @param int                 $parameterIndex
     * @param ReflectionParameter $parameter
     * @param array               $annotationParameters
     *
     * @return string|null Entry name or null if not found.
     */
    private function getMethodParameter($parameterIndex, ReflectionParameter $parameter, array $annotationParameters)
    {
        // @Inject has definition for this parameter (by index, or by name)
        if (isset($annotationParameters[$parameterIndex])) {
            return $annotationParameters[$parameterIndex];
        }
        if (isset($annotationParameters[$parameter->getName()])) {
            return $annotationParameters[$parameter->getName()];
        }

        // Skip optional parameters if not explicitly defined
        if ($parameter->isOptional()) {
            return null;
        }

        // Try to use the type-hinting
        $parameterClass = $parameter->getClass();
        if ($parameterClass) {
            return $parameterClass->getName();
        }

        // Last resort, look for @param tag
        return $this->getPhpDocReader()->getParameterClass($parameter);
    }

    /**
     * @return Reader The annotation reader
     */
    public function getAnnotationReader()
    {
        if ($this->annotationReader === null) {
            AnnotationRegistry::registerLoader('class_exists');
            $this->annotationReader = new SimpleAnnotationReader();
            $this->annotationReader->addNamespace('DI\Annotation');
        }

        return $this->annotationReader;
    }

    /**
     * @return PhpDocReader
     */
    private function getPhpDocReader()
    {
        if ($this->phpDocReader === null) {
            $this->phpDocReader = new PhpDocReader($this->ignorePhpDocErrors);
        }

        return $this->phpDocReader;
    }

    private function readInjectableAnnotation(ReflectionClass $class, ObjectDefinition $definition)
    {
        try {
            /** @var Injectable|null $annotation */
            $annotation = $this->getAnnotationReader()
                ->getClassAnnotation($class, 'DI\Annotation\Injectable');
        } catch (UnexpectedValueException $e) {
            throw new InvalidAnnotation(sprintf(
                'Error while reading @Injectable on %s: %s',
                $class->getName(),
                $e->getMessage()
            ), 0, $e);
        }

        if (! $annotation) {
            return;
        }

        if ($annotation->isLazy() !== null) {
            $definition->setLazy($annotation->isLazy());
        }
    }
}
