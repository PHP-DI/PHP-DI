<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.com/PHP-DI/
 * @copyright 2012 Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Annotations;

/**
 * "Inject" annotation
 * @Annotation
 * @Target({"METHOD","PROPERTY"})
 */
final class Inject
{

    /**
     * Bean name
     * @var string
     */
    private $name;

    /**
     * @var boolean
     */
    private $lazy;

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
        if (isset($values['lazy']) && is_bool($values['lazy'])) {
            $this->lazy = $values['lazy'];
        }

        // @Inject on a method
        if (isset($values['value']) && is_array($values['value'])) {
            foreach ($values['value'] as $key => $value) {
                // This is a shortcut to a full parameter definition
                if (is_string($value)) {
                    $value = array('name' => $value);
                }
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
     * @return bool
     */
    public function isLazy()
    {
        return $this->lazy;
    }

    /**
     * @return array Parameters, indexed by the parameter number (index) or name
     */
    public function getParameters()
    {
        return $this->parameters;
    }

}
