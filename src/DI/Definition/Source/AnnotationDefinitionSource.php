<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.io/PHP-DI/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Definition\Source;

use DI\Annotation\Inject;
use DI\Annotation\Injectable;
use DI\Definition\ClassDefinition;
use DI\Definition\EntryReference;
use DI\Definition\Exception\AnnotationException;
use DI\Definition\Exception\DefinitionException;
use DI\Definition\ClassInjection\MethodInjection;
use DI\Definition\ClassInjection\PropertyInjection;
use DI\Definition\ClassInjection\UndefinedInjection;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Annotations\SimpleAnnotationReader;
use InvalidArgumentException;
use PhpDocReader\PhpDocReader;
use ReflectionClass;
use ReflectionMethod;
use UnexpectedValueException;

/**
 * Source of DI class definitions in annotations such as @ Inject and @ var annotations.
 *
 * Uses Reflection, Doctrine's Annotations and regex docblock parsing.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class AnnotationDefinitionSource implements DefinitionSource
{
    /**
     * @var Reader
     */
    private $annotationReader;

    /**
     * @var PhpDocReader
     */
    private $phpDocReader;

    /**
     * {@inheritdoc}
     * @throws AnnotationException
     * @throws InvalidArgumentException The class doesn't exist
     */
    public function getDefinition($name)
    {
        if (!$this->classExists($name)) {
            return null;
        }

        $reflectionClass = new ReflectionClass($name);
        $classDefinition = new ClassDefinition($name);

        // Injectable annotation
        /** @var $injectableAnnotation Injectable */
        try {
            $injectableAnnotation = $this->getAnnotationReader()
                ->getClassAnnotation($reflectionClass, 'DI\Annotation\Injectable');
        } catch (UnexpectedValueException $e) {
            throw new DefinitionException(sprintf(
                'Error while reading @Injectable on %s: %s',
                $reflectionClass->getName(),
                $e->getMessage()
            ));
        }

        if ($injectableAnnotation) {
            if ($injectableAnnotation->getScope()) {
                $classDefinition->setScope($injectableAnnotation->getScope());
            }
            if ($injectableAnnotation->isLazy() !== null) {
                $classDefinition->setLazy($injectableAnnotation->isLazy());
            }
        }

        // Browse the class properties looking for annotated properties
        $this->readProperties($reflectionClass, $classDefinition);

        // Look for annotated constructor
        $constructor = $reflectionClass->getConstructor();
        if ($constructor) {
            $this->readConstructor($constructor, $classDefinition);
        }

        // Browse the object's methods looking for annotated methods
        $this->readMethods($reflectionClass, $classDefinition);

        return $classDefinition;
    }

    /**
     * Browse the class properties looking for annotated properties.
     *
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
            $annotation = $this->getAnnotationReader()->getPropertyAnnotation($property, 'DI\Annotation\Inject');
            if ($annotation === null) {
                continue;
            }

            /** @var $annotation Inject */
            $entryName = $annotation->getName();

            // Look for @var content
            $entryName = $entryName ?: $this->getPhpDocReader()->getPropertyType($property);

            if ($entryName === null) {
                $value = new UndefinedInjection();
            } else {
                $value = new EntryReference($entryName);
            }

            $classDefinition->addPropertyInjection(
                new PropertyInjection($property->name, $value)
            );
        }
    }

    /**
     * @param ReflectionMethod $reflectionMethod
     * @param ClassDefinition  $classDefinition
     */
    private function readConstructor(ReflectionMethod $reflectionMethod, ClassDefinition $classDefinition)
    {
        // Look for @Inject annotation
        /** @var $annotation Inject|null */
        $annotation = $this->getAnnotationReader()->getMethodAnnotation($reflectionMethod, 'DI\Annotation\Inject');

        if ($annotation) {
            // @Inject found, create MethodInjection
            $parameters = $this->readMethodParameters($reflectionMethod, $annotation);
            $classDefinition->setConstructorInjection(
                new MethodInjection($reflectionMethod->name, $parameters)
            );
        }
    }

    /**
     * Browse the object's methods looking for annotated methods.
     *
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

            $this->readMethod($method, $classDefinition);
        }
    }

    /**
     * Browse the object's methods looking for annotated methods.
     *
     * @param ReflectionMethod $reflectionMethod
     * @param ClassDefinition  $classDefinition
     */
    private function readMethod(ReflectionMethod $reflectionMethod, ClassDefinition $classDefinition)
    {
        // Look for @Inject annotation
        /** @var $annotation Inject|null */
        $annotation = $this->getAnnotationReader()->getMethodAnnotation($reflectionMethod, 'DI\Annotation\Inject');

        if ($annotation) {
            // @Inject found, create MethodInjection
            $parameters = $this->readMethodParameters($reflectionMethod, $annotation);
            $classDefinition->addMethodInjection(
                new MethodInjection($reflectionMethod->name, $parameters)
            );
        }
    }

    /**
     * @param ReflectionMethod $method
     * @param Inject           $annotation
     *
     * @return array
     */
    private function readMethodParameters(ReflectionMethod $method, Inject $annotation)
    {
        $annotationParameters = $annotation->getParameters();

        $parameters = array();
        foreach ($method->getParameters() as $index => $parameter) {

            $entryName = null;

            // @Inject has definition for this parameter
            if (isset($annotationParameters[$index])) {
                $entryName = $annotationParameters[$index];
            }

            // Look for @param tag or PHP type-hinting (only case where we use reflection)
            if ($entryName === null) {
                $entryName = $this->getPhpDocReader()->getParameterType($parameter);
            }

            if ($entryName === null) {
                $parameters[] = new UndefinedInjection();
                continue;
            }

            $parameters[] = new EntryReference($entryName);
        }

        return $parameters;
    }

    /**
     * @return Reader The annotation reader
     */
    public function getAnnotationReader()
    {
        if ($this->annotationReader == null) {
            AnnotationRegistry::registerAutoloadNamespace('DI\Annotation', __DIR__ . '/../../../');
            $this->annotationReader = new SimpleAnnotationReader();
            $this->annotationReader->addNamespace('DI\Annotation');
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
     * @param string $class
     * @return bool
     */
    private function classExists($class)
    {
        return class_exists($class) || interface_exists($class);
    }

    /**
     * @return PhpDocReader
     */
    private function getPhpDocReader()
    {
        if ($this->phpDocReader === null) {
            $this->phpDocReader = new PhpDocReader();
        }

        return $this->phpDocReader;
    }
}
