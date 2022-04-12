---
layout: documentation
current_menu: definition-introduction
---

# Definitions

To let PHP-DI know what to inject and where, you have several options:

- use [autowiring](autowiring.md)
- use [attributes](attributes.md)
- use [PHP definitions](php-definitions.md)

You can also use several or all these options at the same time if you want to.

If you combine several sources, there are priorities that apply. From the highest priority to the least:

- Explicit definition on the container (i.e. defined with `$container->set()`)
- PHP file definitions (if you add several configuration files, then the last one can override entries from the previous ones)
- PHP attributes
- Autowiring

Read more in the [Definition overriding documentation](definition-overriding.md)


## Autowiring

See the dedicated documentation about [autowiring](autowiring.md).

## Attributes

See the dedicated documentation about [attributes](attributes.md).

## PHP configuration

See the dedicated documentation about [PHP definitions](php-definitions.md).
