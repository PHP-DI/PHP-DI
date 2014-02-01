---
template: blogpost
title: PHP-DI 3.4 released
author: Matthieu Napoli
date: September 24th 2013
---

I am happy to announce that PHP-DI version 3.4 has just been released.

This time, several small improvements:

- You can now define arrays of values thanks to [@unkind](https://github.com/unkind)

Here is an example using YAML:

```yaml
value4:
  - bob@acme.example.com
  - alice@acme.example.com
```

Note that the arrays can't be associative arrays, and must not be empty.

- `ContainerBuilder` is now fluent thanks to [@drdamour](https://github.com/drdamour):

```php
$builder = new ContainerBuilder();
$builder->useReflection(false)
   ->useAnotations(true)
   ->setCache(  ...cache );
```

- Support for optional parameters (before 3.4, PHP-DI required that they were defined):

```php
public function __construct($name = 'foo')
```

If the `$name` parameter is not defined, then its default value will be used, just like a standard PHP method call.

## Change log

Read all the changes and their authors in the [change log](../change-log.md).
