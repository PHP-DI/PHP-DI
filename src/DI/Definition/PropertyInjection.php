<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.io/PHP-DI/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Definition;

/**
 * Describe an injection in a class property
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
     * Name of the entry that should be injected in the property
     * @var string
     */
    private $entryName;

    /**
     * If the injected object should be a proxy for lazy-loading
     * @var boolean
     */
    private $lazy;

    /**
     * @param string  $propertyName Property name
     * @param string  $entryName Name of the entry that should be injected in the property
     * @param boolean $lazy If the injected object should be a proxy for lazy-loading
     */
    public function __construct($propertyName, $entryName, $lazy = false)
    {
        $this->propertyName = (string) $propertyName;
        $this->entryName = (string) $entryName;
        $this->setLazy($lazy);
    }

    /**
     * @return string Property name
     */
    public function getPropertyName()
    {
        return $this->propertyName;
    }

    /**
     * @return string Name of the entry that should be injected in the property
     */
    public function getEntryName()
    {
        return $this->entryName;
    }

    /**
     * @return boolean If the injected object should be a proxy for lazy-loading
     */
    public function isLazy()
    {
        return $this->lazy;
    }

    /**
     * @param boolean $lazy If the injected object should be a proxy for lazy-loading
     */
    public function setLazy($lazy)
    {
        $this->lazy = (boolean) $lazy;
    }

    /**
     * Merge another definition into the current definition
     *
     * In case of conflicts, the latter prevails (i.e. the other definition)
     *
     * @param PropertyInjection $propertyInjection
     */
    public function merge(PropertyInjection $propertyInjection)
    {
        if ($propertyInjection->entryName !== null) {
            $this->entryName = $propertyInjection->entryName;
        }
        if ($propertyInjection->lazy !== null) {
            $this->lazy = $propertyInjection->lazy;
        }
    }

}
