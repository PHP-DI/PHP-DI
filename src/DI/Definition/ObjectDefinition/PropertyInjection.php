<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Definition\ObjectDefinition;

/**
 * Describe an injection in a class property.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class PropertyInjection
{
    /**
     * Property name
     * @var string
     */
    private $propertyName;

    /**
     * Value that should be injected in the property
     * @var mixed
     */
    private $value;

    /**
     * @param string $propertyName Property name
     * @param mixed  $value Value that should be injected in the property
     */
    public function __construct($propertyName, $value)
    {
        $this->propertyName = (string) $propertyName;
        $this->value = $value;
    }

    /**
     * @return string Property name
     */
    public function getPropertyName()
    {
        return $this->propertyName;
    }

    /**
     * @return string Value that should be injected in the property
     */
    public function getValue()
    {
        return $this->value;
    }
}
