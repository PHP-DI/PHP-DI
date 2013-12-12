<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Definition\Source;

use DI\Definition\Exception\DefinitionException;
use DI\Definition\MergeableDefinition;

/**
 * Reads DI definitions from a file returning a PHP array.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class PHPFileDefinitionSource extends ArrayDefinitionSource
{
    /**
     * @var bool
     */
    private $initialized = false;

    /**
     * File containing definitions, or null if the definitions are given as a PHP array.
     * @var string|null
     */
    private $file;

    /**
     * @param string $file File in which the definitions are returned as an array.
     */
    public function __construct($file)
    {
        // Lazy-loading to improve performances
        $this->file = $file;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefinition($name, MergeableDefinition $parentDefinition = null)
    {
        $this->initialize();

        return parent::getDefinition($name, $parentDefinition);
    }

    /**
     * Lazy-loading of the definitions.
     * @throws DefinitionException
     */
    private function initialize()
    {
        if ($this->initialized === true) {
            return;
        }

        if (! is_readable($this->file)) {
            throw new DefinitionException("File {$this->file} doesn't exist or is not readable");
        }

        $definitions = require $this->file;

        if (! is_array($definitions)) {
            throw new DefinitionException("File {$this->file} should return an array of definitions");
        }

        $this->addDefinitions($definitions);

        $this->initialized = true;
    }
}
