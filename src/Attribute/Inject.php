<?php

declare(strict_types=1);

namespace DI\Attribute;

use Attribute;
use DI\Definition\Exception\InvalidAnnotation;

/**
 * #[Inject] attribute.
 *
 * Marks a property or method as an injection point
 *
 * @api
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_METHOD)]
final class Inject
{
    /**
     * Entry name.
     */
    private ?string $name = null;

    /**
     * Parameters, indexed by the parameter number (index) or name.
     *
     * Used if the annotation is set on a method
     */
    private array $parameters = [];

    /**
     * @param string|array|null $name
     *
     * @throws InvalidAnnotation
     */
    public function __construct($name = null)
    {
        // #[Inject('foo')] or #[Inject(name: 'foo')]
        if (is_string($name)) {
            $this->name = $name;
        }

        // #[Inject([...])] on a method
        if (is_array($name)) {
            foreach ($name as $key => $value) {
                if (! is_string($value)) {
                    throw new InvalidAnnotation(sprintf(
                        "#[Inject(['param' => 'value'])] expects \"value\" to be a string, %s given.",
                        json_encode($value, JSON_THROW_ON_ERROR)
                    ));
                }

                $this->parameters[$key] = $value;
            }
        }
    }

    /**
     * @return string|null Name of the entry to inject
     */
    public function getName() : ?string
    {
        return $this->name;
    }

    /**
     * @return array Parameters, indexed by the parameter number (index) or name
     */
    public function getParameters() : array
    {
        return $this->parameters;
    }
}
