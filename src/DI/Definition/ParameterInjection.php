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
 * Describe an injection through a method parameter
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class ParameterInjection
{

    /**
     * Parameter name or index
     * @var string|int
     */
    private $parameterName;

    /**
     * Name of the entry that should be injected through the parameter
     * @var string|null
     */
    private $entryName;

    /**
     * @param string|int  $parameterName Parameter name or index
     * @param string|null $entryName Name of the entry that should be injected through the parameter
     */
    public function __construct($parameterName, $entryName = null) {
        $this->parameterName = $parameterName;
        $this->entryName = $entryName;
    }

    /**
     * @return string|int Parameter name or index
     */
    public function getParameterName() {
        return $this->parameterName;
    }

    /**
     * @return string|null Name of the entry that should be injected through the parameter
     */
    public function getEntryName() {
        return $this->entryName;
    }

    /**
     * Merge another definition into the current definition
     *
     * In case of conflicts, the latter prevails (i.e. the other definition)
     *
     * @param ParameterInjection $parameterInjection
     */
    public function merge(ParameterInjection $parameterInjection) {
        if ($parameterInjection->entryName !== null) {
            $this->entryName = $parameterInjection->entryName;
        }
    }

}
