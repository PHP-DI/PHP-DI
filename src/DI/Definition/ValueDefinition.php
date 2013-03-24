<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.com/PHP-DI/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Definition;

/**
 * Definition of a value for dependency injection
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class ValueDefinition implements Definition
{

    /**
     * Entry name
     * @var string
     */
    private $name;

    /**
     * @var mixed
     */
    private $value;

    /**
     * @param string $name Entry name
     * @param mixed $value
     */
    public function __construct($name, $value)
    {
        $this->name = $name;
        $this->value = $value;
    }

    /**
     * @return string Entry nam
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * {@inheritdoc}
     */
    public function merge(Definition $definition)
    {
        if ($definition instanceof ValueDefinition) {
            // The latter prevails
            $this->value = $definition->getValue();
        } else {
            throw new DefinitionException("DI definition conflict: there are 2 different definitions for '"
                . $definition->getName() . "' that are incompatible, they are not of the same type");
        }
    }

}
