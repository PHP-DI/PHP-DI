# Performance tests

These tests are work in progress. They are mostly useful to profile with Blackfire and detect possible improvements.

```
composer update -o --classmap-authoritative
blackfire run --samples=100 php get.php
```

Remember to disable xdebug.

Improvements to do:

- run through webserver with Nginx and php-fpm (so that opcache is used)
- use a VM (e.g. homestead or a vagrant box)
- performance test with `ApcCache`
- add more tests
