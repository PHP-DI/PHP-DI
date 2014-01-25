# PHP-DI 4.0 released

*Posted by [Matthieu Napoli](http://mnapoli.fr) on February 1st 2014*

I am extremely happy to announce that PHP-DI version 4.0 has just been released!

This is a new major release, and it delivers. As indicated by "major release", it comes with BC breaks, but this is for the better.

## Completely new definition format

PHP-DI 3.x offered several definition formats, such as:

- autowiring (use of reflection)
- annotations
- YAML
- PHP array
- various undocumented, unstable formats (XML, JSON)…

PHP-DI 4.0 now offers:

- autowiring (unchanged)
- annotations (very few changes)
- PHP definitions

The last one is completely new, and is the result of several months of brainstorming and trials.

## Code quality

This new major version also comes with even more code quality.

- Better architecture

A large part of the internals of PHP-DI has been rewritten for this new version. The core is now much more maintainable,
testable and extensible. Adding new features should be easier, and many edge cases that couldn't supported in 3.* are now fixed.

- Code coverage

Functional tests have been removed from the code coverage reports, so that only unit tests are taken into account.
Furthermore, every unit test uses the [`@covers` annotation](http://phpunit.de/manual/3.7/en/appendixes.annotations.html#appendixes.annotations.covers) from PHPUnit.

This allows to have a much more realistic and useful code coverage report. To give you an idea, the code coverage
**dropped from 91% to 60%**! A lot of work has been put into testing, and now the code coverage is back up to more than 85%.

- Scrutinizer-CI

The overall Scrutinizer-CI index went from **7.9** to **9.0**! Most issues have been fixed:

![Scrutinizer report](scrutinizer-issues.png)

## Change log

You can also read the complete [change log](../change-log.md).
