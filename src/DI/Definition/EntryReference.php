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
 * Represents a reference to a container entry.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class EntryReference
{
    /**
     * Entry name
     * @var string
     */
    private $name;

    /**
     * @param string $entryName Entry name
     */
    public function __construct($entryName)
    {
        $this->name = $entryName;
    }

    /**
     * @return string Entry name
     */
    public function getName()
    {
        return $this->name;
    }
}
