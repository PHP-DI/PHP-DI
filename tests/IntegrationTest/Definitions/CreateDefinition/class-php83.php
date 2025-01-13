<?php

declare(strict_types=1);

namespace DI\Test\IntegrationTest\Definitions\CreateDefinitionTest;

class Property
{
    public $foo;
}

class ConstructorInjection
{
    public $value;
    public $scalarValue;
    public $typedValue;
    public $typedOptionalValue;
    public $typedOptionalValueDefaultNull;
    /** @var \ProxyManager\Proxy\LazyLoadingInterface */
    public $lazyService;
    public $optionalValue;

    public function __construct(
        $value,
        string $scalarValue,
        \stdClass $typedValue,
        \stdClass $lazyService,
        \stdClass $typedOptionalValue = null,
        ?\stdClass $typedOptionalValueDefaultNull = null,
        $optionalValue = 'hello'
    ) {
        $this->value = $value;
        $this->scalarValue = $scalarValue;
        $this->typedValue = $typedValue;
        $this->typedOptionalValue = $typedOptionalValue;
        $this->typedOptionalValueDefaultNull = $typedOptionalValueDefaultNull;
        $this->lazyService = $lazyService;
        $this->optionalValue = $optionalValue;
    }
}

class PropertyInjection
{
    public $value;
    public $entry;
    /** @var \ProxyManager\Proxy\LazyLoadingInterface */
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
    public $optionalValue;

    public function method(
        $value,
        string $scalarValue,
        \stdClass $typedValue,
        \stdClass $lazyService,
        \stdClass $typedOptionalValue = null,
        ?\stdClass $typedOptionalValueDefaultNull = null,
        $optionalValue = 'hello'
    ) {
        $this->value = $value;
        $this->scalarValue = $scalarValue;
        $this->typedValue = $typedValue;
        $this->typedOptionalValue = $typedOptionalValue;
        $this->typedOptionalValueDefaultNull = $typedOptionalValueDefaultNull;
        $this->lazyService = $lazyService;
        $this->optionalValue = $optionalValue;
    }
}

class PrivatePropertyInjection
{
    private $private;
    protected $protected;

    public function getPrivate()
    {
        return $this->private;
    }

    public function getProtected()
    {
        return $this->protected;
    }
}

class PrivatePropertyInjectionSubClass extends PrivatePropertyInjection
{
    private $private;

    public function getSubClassPrivate()
    {
        return $this->private;
    }
}
