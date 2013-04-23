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

/**
 * XmlDefinitionFileLoader loads XML files definitions.
 *
 * @author Domenic Muskulus <domenic@muskulus.eu>
 */
class XmlDefinitionFileLoader extends DefinitionFileLoader
{

    /**
     * {@inheritdoc}
     */
    public function load($validate = false)
    {
        $definitions = array();

        // NOTE: value is reset in $this->getXmlErrors()
        $internalErrors = libxml_use_internal_errors(true);

        // XMLReader for the first level, as it has a better memory and performance balance.
        $xmlReader = new \XMLReader();

        $xmlReader->open(
            $this->definitionFile,
            'UTF-8',
            LIBXML_NONET | (defined('LIBXML_COMPACT') ? LIBXML_COMPACT : 0)
        );

        if ($validate) {
            $xmlReader->setSchema(__DIR__ . '/schema/definitions-1.0.xsd');
        }

        while ($xmlReader->read()) {
            if ($xmlReader->nodeType == \XMLReader::ELEMENT && $xmlReader->name != 'definitions') {
                $node = $this->toSimpleXml($xmlReader);
                switch ($node->getName()) {
                    case 'value':
                        $this->parseValueDefinition($definitions, $node);
                        break;
                    case 'class':
                        $this->parseClassDefinition($definitions, $node);
                        break;
                    case 'interface':
                        $this->parseInterfaceDefinition($definitions, $node);
                        break;
                }
            }
        }
        $xmlReader->close();

        $errors = $this->getXmlErrors($internalErrors);
        if (!empty($errors)) {
            throw new ParseException(implode("\n", $errors));
        }

        return $definitions;
    }

    /**
     * Parses value definitions
     *
     * @param array             &$definitions
     * @param \SimpleXMLElement $node
     */
    private function parseValueDefinition(&$definitions, $node)
    {
        $definitions[(string) $node['name']] = $this->phpize($node[0]);
    }

    /**
     * Parses a class definition
     *
     * @param array             &$definitions
     * @param \SimpleXMLElement $node
     */
    private function parseClassDefinition(&$definitions, $node)
    {
        $classDefinition = array();

        $this->parseAttributes($classDefinition, $node, array('name'));

        $classDefinition += array(
            'constructor' => array(),
            'methods'     => array(),
            'properties'  => array(),
        );

        foreach ($node->children() as $childNode) {
            switch ($childNode->getName()) {
                case 'constructor':
                    $this->parseConstructorDefinition($classDefinition['constructor'], $childNode);
                    break;
                case 'method':
                    $this->parseMethodDefinition($classDefinition['methods'], $childNode);
                    break;
                case 'property':
                    $this->parsePropertyDefinition($classDefinition['properties'], $childNode);
                    break;
            }
        }

        $definitions[(string) $node['name']] = $classDefinition;
    }

    /**
     * Parses an interface definition/mapping
     *
     * @param array             &$definitions
     * @param \SimpleXMLElement $node
     */
    private function parseInterfaceDefinition(&$definitions, $node)
    {
        $this->parseAttributes($interfaceDefinition, $node, array('name'));
        $definitions[(string) $node['name']] = $interfaceDefinition;
    }

    /**
     * Parses a constructor definition
     *
     * @param array             $definitions
     * @param \SimpleXMLElement $node
     */
    private function parseConstructorDefinition(&$definitions, $node)
    {
        $this->parseArgumentDefinitions($definitions, $node);
    }

    /**
     * Parses a method definition
     *
     * @param array             &$definitions
     * @param \SimpleXMLElement $node
     */
    private function parseMethodDefinition(&$definitions, $node)
    {
        $definitions[(string) $node['name']] = array();
        $this->parseArgumentDefinitions($definitions[(string) $node['name']], $node);
    }

    /**
     * Parses argument definitions of methods and constructors
     *
     * @param array             &$definitions
     * @param \SimpleXMLElement $node
     */
    private function parseArgumentDefinitions(&$definitions, $node)
    {
        foreach ($node->children() as $argument) {
            if ($argument->getName() == 'argument') {
                if (!empty($argument['name'])) {
                    $definitions[(string) $argument['name']] = (string) $argument[0];
                    continue;
                }
                $definitions[] = (string) $argument[0];
            }
        }
    }

    /**
     * Parses a property in class definition
     *
     * @param array             &$definitions
     * @param \SimpleXMLElement $node
     */
    private function parsePropertyDefinition(&$definitions, $node)
    {
        $propertyDefinition = ($node['lazy'] !== null) ? array(
            'name' => (string) $node[0],
            'lazy' => $this->phpize($node['lazy'])
        ) : (string) $node[0];

        $definitions[(string) $node['name']] = $propertyDefinition;
    }

    /**
     * Parses all attributes of a node
     *
     * @param array             &$definition
     * @param \SimpleXMLElement $node
     * @param array             $exclude Array of attribute names to exclude from parsing
     */
    private function parseAttributes(&$definition, $node, $exclude = array())
    {
        foreach ($node->attributes() as $attributeName => $attribute) {
            if (!in_array($attributeName, $exclude)) {
                $this->parseAttribute($definition, $attributeName, $node);
            }
        }
    }

    /**
     * @param array             &$definition
     * @param string            $attributeName
     * @param \SimpleXMLElement $node
     * @return bool If a value was assigned
     */
    private function parseAttribute(&$definition, $attributeName, $node)
    {
        $attribute = $node[$attributeName];
        if ($attribute !== null) {
            $definition[$attributeName] = $this->phpize($attribute);
            return true;
        }
        return false;
    }

    /**
     * Converts an xml value to a php type.
     *
     * @param mixed $value
     * @return mixed
     */
    public function phpize($value)
    {
        $value = (string) $value;
        $lowercaseValue = strtolower($value);

        switch (true) {
            case 'null' === $lowercaseValue:
                return null;
            case ctype_digit($value):
                $raw = $value;
                $cast = intval($value);

                return '0' == $value[0] ? octdec($value) : (((string) $raw == (string) $cast) ? $cast : $raw);
            case 'true' === $lowercaseValue:
                return true;
            case 'false' === $lowercaseValue:
                return false;
            case is_numeric($value):
                return '0x' == $value[0] . $value[1] ? hexdec($value) : floatval($value);
            case preg_match('/^(-|\+)?[0-9,]+(\.[0-9]+)?$/', $value):
                return floatval(str_replace(',', '', $value));
            default:
                return $value;
        }
    }

    /**
     * @param \XMLReader $xmlReader
     * @return \SimpleXMLElement
     */
    private function toSimpleXml($xmlReader)
    {
        $doc = new \DOMDocument('1.0', 'UTF-8');
        return simplexml_import_dom($doc->importNode($xmlReader->expand(), true));
    }

    /**
     * @param bool $internalErrors
     * @return array
     */
    private function getXmlErrors($internalErrors)
    {
        $errors = array();
        foreach (libxml_get_errors() as $error) {
            $errors[] = sprintf(
                '[%s %s] %s (in %s - line %d, column %d)',
                LIBXML_ERR_WARNING == $error->level ? 'WARNING' : 'ERROR',
                $error->code,
                trim($error->message),
                $error->file ? $error->file : basename($this->definitionFile),
                $error->line,
                $error->column
            );
        }

        libxml_clear_errors();
        libxml_use_internal_errors($internalErrors);

        return $errors;
    }

}
