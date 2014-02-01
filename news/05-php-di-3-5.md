---
template: blogpost
title: PHP-DI 3.5 released
author: Matthieu Napoli
date: October 11th 2013
---

I am happy to announce that PHP-DI version 3.5 has just been released.

It features a minor but amazing improvement: **you don't have to import annotations anymore**!

**Before**:

```php
<?php

use DI\Annotation\Inject;

class ProductController
{
    /**
     * @Inject
     * @var ProductService
     */
    private $productService;

    // ...
}
```

**After**:

```php
<?php

class ProductController
{
    /**
     * @Inject
     * @var ProductService
     */
    private $productService;

    // ...
}
```

Of course, you don't have to change you code, everything will still work if you import annotations.

This is made possible thanks to the amazing [Doctrine Annotation](https://github.com/doctrine/annotations) library.

Related to the same change, [#124](https://github.com/mnapoli/PHP-DI/issues/124) is now fixed, that means **there is
no conflict with other annotations anymore**.

For example, you can now use `@Inject` in PHPUnit, the `@test` and `@expectedException` annotations are not a problem.

## Change log

You can also read the complete [change log](../change-log.md).
