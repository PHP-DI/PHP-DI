<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.io/PHP-DI/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Definition\FileLoader;

use DI\Definition\FileLoader\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

/**
 * YamlDefinitionFileLoader loads YAML files definitions.
 *
 * @author Domenic Muskulus <domenic@muskulus.eu>
 */
class YamlDefinitionFileLoader extends DefinitionFileLoader
{

    /**
     * {@inheritdoc}
     */
    public function load()
    {
        try {
            $definitions = Yaml::parse($this->definitionFile);
        } catch (\Exception $e) {
            throw new ParseException($e->getMessage());
        }

        // Fix empty elements (to array)
        foreach ($definitions as $key => $value) {
            if ($value === null) {
                $definitions[$key] = array();
            }
        }

        return $definitions;
    }

}
