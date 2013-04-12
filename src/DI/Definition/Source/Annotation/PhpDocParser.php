<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.io/PHP-DI/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Definition\Source\Annotation;

use DI\Definition\Source\AnnotationException;
use Doctrine\Common\Annotations\PhpParser;
use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionProperty;

/**
 * PhpDoc parser
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class PhpDocParser
{

    /**
     * @var PhpParser
     */
    private $phpParser;


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->phpParser = new PhpParser();
    }

    /**
     * Parse the docblock of the property to get the var annotation
     * @param ReflectionClass    $class
     * @param ReflectionProperty $property
     * @throws AnnotationException
     * @return string|null Type of the property (content of var annotation)
     */
    public function getPropertyType(ReflectionClass $class, ReflectionProperty $property)
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
    public function getParameterType(ReflectionClass $class, ReflectionMethod $method, ReflectionParameter $parameter)
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
