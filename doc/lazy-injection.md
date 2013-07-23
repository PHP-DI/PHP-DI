# Lazy injection

Consider the following example:

```php
<?php
class Foo
{
    private $a;
    private $b;

    public function __construct(Foo $a, Bar $b) {
        $this->a = $a;
        $this->b = $b;
    }

    public function doSomething() {
        $this->a->doStuff();
    }

    public function doSomethingElse() {
        $this->b->doStuff();
    }
}
```

`a` is used only when `doSomething()` is called, and `b` only when `doSomethingElse()` is called.

You may wonder then: why injecting `a` **and** `b` if they may not be used? Especially if creating those objects is heavy in time or memory.

That's where lazy injection can help.

## How it works

If you define a dependency as "lazy", PHP-DI will inject:

- the object, if it has already been created
- or else a **proxy** to the object, if it is not yet created

The proxy is a special kind of object that **looks and behave exactly like the original object**, so you can't tell the difference. The proxy will instantiate the original object only when needed.

Creating a proxy is complex. For this, PHP-DI relies on [ProxyManager](https://github.com/Ocramius/ProxyManager), the (amazing) library used by Doctrine, Symfony and Zend.

### Example

For the simplicity of the example, we will not inject a lazy object, but we will ask the container to return one:

```php
// $proxy is a Proxy object, it is not initialized
// It is very lightweight in memory
$proxy = $container->get('My\Class', true);

var_dump($proxy instanceof \My\Class); // true

// Calling a method on the proxy will initialize it
$proxy->doSomething(); // works if doSomething() is a method of My\Class
// Now the proxy is initialized, the real instance of My\Class has been created and called
```

## How to use

Here is how you can define lazy injections depending on the configuration you use.

### Annotations

```php
<?php
use DI\Annotation\Inject;

class Example {
    /**
     * @Inject(lazy=true)
     * @var My\Class
     */
    protected $property;

    /**
     * @Inject({ "param1" = {"lazy"=true} })
     */
    public function method(My\Class $param1) {
    }
}
```

### PHP code

```php
<?php
$containerPHP->set('Example')
    ->withProperty('property', 'My\Class', true)
    ->withMethod('method', array('param1' => array(
            'name' => 'My\Class',
            'lazy' => true,
        )));
```

### PHP array

```php
<?php
return array(
    'Example' => array(
        'properties'  => array(
            'property' => array(
                'name' => 'My\Class',
                'lazy' => true,
            ),
        ),
        'methods'     => array(
            'method' => array(
                'param1' => array(
                    'name' => 'My\Class',
                    'lazy' => true,
                ),
            ),
        ),
    ),
);
```

### YAML

```yaml
Example:
  properties:
    property:
      name: My\Class
      lazy: true
  methods:
    method:
      param1:
        name: My\Class
        lazy: true
```
