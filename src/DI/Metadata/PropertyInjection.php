<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.com/PHP-DI/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Metadata;

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
        $this->lazy = (boolean) $lazy;
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

}
