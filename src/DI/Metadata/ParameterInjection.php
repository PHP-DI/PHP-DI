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
 * Describe an injection through a method parameter
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class ParameterInjection
{

    /**
     * Parameter name
     * @var string
     */
    private $parameterName;

    /**
     * Name of the entry that should be injected through the parameter
     * @var string
     */
    private $entryName;

    /**
     * @param string $parameterName Parameter name
     * @param string $entryName Name of the entry that should be injected through the parameter
     */
    public function __construct($parameterName, $entryName)
    {
        $this->parameterName = (string) $parameterName;
        $this->entryName = (string) $entryName;
    }

    /**
     * @return string Parameter name
     */
    public function getParameterName()
    {
        return $this->parameterName;
    }

    /**
     * @return string Name of the entry that should be injected through the parameter
     */
    public function getEntryName()
    {
        return $this->entryName;
    }

}
