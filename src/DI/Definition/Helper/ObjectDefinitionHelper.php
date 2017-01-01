<?php

namespace DI\Definition\Helper;

use DI\Definition\LegacyObjectDefinition;

/**
 * Helps defining how to create an instance of a class.
 *
 * @deprecated Use CreateDefinitionHelper instead.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class ObjectDefinitionHelper extends CreateDefinitionHelper
{
    const DEFINITION_CLASS = LegacyObjectDefinition::class;
}
