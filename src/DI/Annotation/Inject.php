<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.io/PHP-DI/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Annotation;

/**
 * "Inject" annotation
 *
 * Marks a property or method as an injection point
 *
 * @Annotation
 * @Target({"METHOD","PROPERTY"})
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
final class Inject
{
    /**
     * Entry name
     * @var string
     */
    private $name;

    /**
     * Parameters, indexed by the parameter number (index) or name
     *
     * Used if the annotation is set on a method
     * @var array
     */
    private $parameters = array();

    /**
     * @param array $values
     */
    public function __construct(array $values)
    {
        // Process the parameters as a list AND as a parameter array (we don't know on what the annotation is)

        // @Inject on a property
        if (isset($values['name']) && is_string($values['name'])) {
            $this->name = $values['name'];
        } elseif (isset($values['value']) && is_string($values['value'])) {
            $this->name = $values['value'];
        }

        // @Inject on a method
        if (isset($values['value']) && is_array($values['value'])) {
            foreach ($values['value'] as $key => $value) {
                $this->parameters[$key] = $value;
            }
        }
    }

    /**
     * @return string Name of the entry to inject
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return array Parameters, indexed by the parameter number (index) or name
     */
    public function getParameters()
    {
        return $this->parameters;
    }
}
