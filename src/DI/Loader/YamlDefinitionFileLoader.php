<?php
/**
 * PHP-DI
 *
 * @link      http://mnapoli.github.io/PHP-DI/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Loader;

use DI\Loader\Exception\ParseException;
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

        return $definitions;
    }

}
