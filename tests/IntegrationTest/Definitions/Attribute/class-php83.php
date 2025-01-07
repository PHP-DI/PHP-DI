<?php

declare(strict_types=1);

namespace DI\Test\IntegrationTest\Definitions\AttributesTest;

use DI\Attribute\Inject;
use stdClass;

class NonAnnotatedClass
{
}

class AutowiredClass
{
    public stdClass $entry;
    public function __construct(stdClass $entry)
    {
        $this->entry = $entry;
    }
}

class ConstructorInjection
{
    public $value;
    public string $scalarValue;
    public stdClass $typedValue;
    public ?stdClass $typedOptionalValue;
    public ?stdClass $typedOptionalValueDefaultNull;
    /** @var stdClass&\ProxyManager\Proxy\LazyLoadingInterface */
    public $lazyService;
    public stdClass $attribute;
    public string $optionalValue;

    #[Inject(['value' => 'foo', 'scalarValue' => 'foo', 'lazyService' => 'lazyService'])]
    public function __construct(
        $value,
        string $scalarValue,
        \stdClass $typedValue,
        \stdClass $lazyService,
        #[Inject('attribute')]
        \stdClass $attribute,
        \stdClass $typedOptionalValue = null,
        ?\stdClass $typedOptionalValueDefaultNull = null,
        string $optionalValue = 'hello'
    ) {
        $this->value = $value;
        $this->scalarValue = $scalarValue;
        $this->typedValue = $typedValue;
        $this->typedOptionalValue = $typedOptionalValue;
        $this->typedOptionalValueDefaultNull = $typedOptionalValueDefaultNull;
        $this->lazyService = $lazyService;
        $this->attribute = $attribute;
        $this->optionalValue = $optionalValue;
    }
}

class PropertyInjection
{
    #[Inject(name: 'foo')]
    public $value;
    #[Inject('foo')]
    public $value2;
    #[Inject]
    public stdClass $entry;
    #[Inject('lazyService')]
    public $lazyService;
}

class MethodInjection
{
    public $value;
    public $scalarValue;
    public $typedValue;
    public $typedOptionalValue;
    public $typedOptionalValueDefaultNull;
    /** @var \ProxyManager\Proxy\LazyLoadingInterface */
    public $lazyService;
    public stdClass $attribute;
    public $optionalValue;

    #[Inject(['value' => 'foo', 'scalarValue' => 'foo', 'lazyService' => 'lazyService'])]
    public function method(
        $value,
        string $scalarValue,
        $untypedValue,
        \stdClass $lazyService,
        #[Inject('attribute')]
        stdClass $attribute,
        \stdClass $typedOptionalValue = null,
        ?\stdClass $typedOptionalValueDefaultNull = null,
        $optionalValue = 'hello'
    ) {
        $this->value = $value;
        $this->scalarValue = $scalarValue;
        $this->untypedValue = $untypedValue;
        $this->typedOptionalValue = $typedOptionalValue;
        $this->typedOptionalValueDefaultNull = $typedOptionalValueDefaultNull;
        $this->lazyService = $lazyService;
        $this->attribute = $attribute;
        $this->optionalValue = $optionalValue;
    }
}
