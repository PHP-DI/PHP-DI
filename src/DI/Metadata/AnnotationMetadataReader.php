<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.com/PHP-DI/
 * @copyright 2012 Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Metadata;

use DI\Annotations\AnnotationException;
use DI\Annotations\Inject;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\PhpParser;
use Doctrine\Common\Annotations\Reader;
use InvalidArgumentException;

/**
 * Reads PHP class metadata in annotations such as @ Inject and @ var annotations
 *
 * Uses Reflection, Doctrine's Annotations and regex docblock parsing
 */
class AnnotationMetadataReader implements MetadataReader
{

    /**
     * @var \Doctrine\Common\Annotations\Reader
     */
    private $annotationReader;

    /**
     * @var \Doctrine\Common\Annotations\PhpParser
     */
    private $phpParser;


    public function __construct()
    {
        $this->phpParser = new PhpParser();
    }

    /**
     * Returns DI annotations found in the class
     * @param string $classname
     * @throws \DI\Annotations\AnnotationException
     * @throws \InvalidArgumentException The class doesn't exist
     * @return ClassMetadata
     */
    public function getClassMetadata($classname)
    {
        if (!class_exists($classname)) {
            throw new InvalidArgumentException("The class $classname doesn't exist");
        }
        $reflectionClass = new \ReflectionClass($classname);

        $classMetadata = new ClassMetadata();

        // Scope annotation
        $scopeAnnotation = $this->getAnnotationReader()->getClassAnnotation($reflectionClass, 'DI\Annotations\Scope');
        if ($scopeAnnotation !== null && $scopeAnnotation->value) {
            $classMetadata->setScope($scopeAnnotation->value);
        }

        // Browse the object's properties looking for annotated properties
        foreach ($reflectionClass->getProperties() as $property) {

            // Ignore static properties
            if ($property->isStatic()) {
                continue;
            }

            // Look for DI annotations
            $propertyAnnotations = $this->getAnnotationReader()->getPropertyAnnotations($property);
            foreach ($propertyAnnotations as $annotation) {
                // @Inject
                if ($annotation instanceof Inject) {
                    // Enrich @Inject annotation with @var content
                    if ($annotation->name == null) {
                        $parameterType = $this->getPropertyType($reflectionClass, $property);
                        if ($parameterType == null) {
                            throw new AnnotationException("@Inject was found on $classname::"
                                . $property->getName() . " but no (or empty) @var annotation");
                        }
                        $annotation->name = $parameterType;
                    }
                    $classMetadata->setPropertyInjection($property->getName(), $annotation->name);
                    break;
                }
            }

        }

        // Browse the object's methods looking for annotated methods
        foreach ($reflectionClass->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {

            // Ignore static methods
            if ($method->isStatic()) {
                continue;
            }

            // Look for DI annotations
            $methodAnnotations = $this->getAnnotationReader()->getMethodAnnotations($method);
            foreach ($methodAnnotations as $annotation) {
                // @Inject
                if ($annotation instanceof Inject) {
                    if ($method->getNumberOfParameters() != 1) {
                        throw new AnnotationException("@Inject was found on $classname::"
                            . $method->name . "(), the method should have exactly one parameter");
                    }
                    $parameters = $method->getParameters();
                    /** @var $parameter \ReflectionParameter */
                    $parameter = current($parameters);
                    // Enrich @Inject annotation with @var content
                    if ($annotation->name == null) {
                        $parameterType = $this->getParameterType($reflectionClass, $method, $parameter);
                        if ($parameterType == null) {
                            throw new AnnotationException("@Inject was found on $classname::"
                                . $method->name . "() but the parameter $" . $parameter->name
                                . " has no type: impossible to deduce its type");
                        }
                        $annotation->name = $parameterType;
                    }
                    // Only 1 parameter taken into account for now
                    $injectionParameters = array($parameter->name => $annotation->name);
                    $classMetadata->setMethodInjection($method->name, $injectionParameters);
                    break;
                }
            }

        }

        return $classMetadata;
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
     * @param \ReflectionClass    $class
     * @param \ReflectionProperty $property
     * @throws \DI\Annotations\AnnotationException
     * @return string|null Type of the property (content of var annotation)
     */
    private function getPropertyType(\ReflectionClass $class, \ReflectionProperty $property)
    {
        // Get the content of the @var annotation
        if (preg_match('/@var\s+([^\s]+)/', $property->getDocComment(), $matches)) {
            list(, $type) = $matches;
        } else {
            return null;
        }

        // If the class name is not fully qualified (FQN must start with a \)
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

        return $type;
    }

    /**
     * @param \ReflectionClass     $class
     * @param \ReflectionMethod    $method
     * @param \ReflectionParameter $parameter
     * @return string|null Type of the parameter
     */
    private function getParameterType(\ReflectionClass $class, \ReflectionMethod $method, \ReflectionParameter $parameter)
    {
        $reflectionClass = $parameter->getClass();
        if ($reflectionClass === null) {
            return null;
        }
        return $reflectionClass->name;
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
