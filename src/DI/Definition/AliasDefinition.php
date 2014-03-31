<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Definition;

use DI\Scope;

/**
 * Defines an alias from an entry to another.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class AliasDefinition implements CacheableDefinition
{
    /**
     * Entry name
     * @var string
     */
    private $name;

    /**
     * Name of the target entry
     * @var string
     */
    private $targetEntryName;

    /**
     * @param string $name            Entry name
     * @param string $targetEntryName Name of the target entry
     */
    public function __construct($name, $targetEntryName)
    {
        $this->name = $name;
        $this->targetEntryName = $targetEntryName;
    }

    /**
     * @return string Entry name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getScope()
    {
        return Scope::PROTOTYPE();
    }

    /**
     * @return string
     */
    public function getTargetEntryName()
    {
        return $this->targetEntryName;
    }
}
