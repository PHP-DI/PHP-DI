<?php

namespace DI\Cache;

use Psr\SimpleCache\CacheInterface;

/**
 * Simple implementation of a cache based on an array.
 *
 * The code is based on Doctrine's ArrayCache provider.
 *
 * @link www.doctrine-project.org
 * @author Benjamin Eberlei <kontakt@beberlei.de>
 * @author Guilherme Blanco <guilhermeblanco@hotmail.com>
 * @author Jonathan Wage <jonwage@gmail.com>
 * @author Roman Borschel <roman@code-factory.org>
 * @author David Abdemoulaie <dave@hobodave.com>
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class ArrayCache implements CacheInterface
{
    /**
     * @var array
     */
    private $data = [];

    public function get($key, $default = null)
    {
        // isset() is required for performance optimizations, to avoid unnecessary function calls to array_key_exists.
        if (isset($this->data[$key]) || array_key_exists($key, $this->data)) {
            return $this->data[$key];
        }

        return $default;
    }

    public function has($key)
    {
        // isset() is required for performance optimizations, to avoid unnecessary function calls to array_key_exists.
        return isset($this->data[$key]) || array_key_exists($key, $this->data);
    }

    public function set($key, $value, $ttl = 0)
    {
        $this->data[$key] = $value;

        return true;
    }

    public function delete($key)
    {
        unset($this->data[$key]);

        return true;
    }

    public function clear()
    {
        $this->data = [];

        return true;
    }

    public function getMultiple($keys, $default = null)
    {
        $values = [];
        foreach ($keys as $key) {
            $values[$key] = $this->get($key, $default);
        }

        return $values;
    }

    public function setMultiple($values, $ttl = null)
    {
        foreach ($values as $key => $value) {
            $this->data[$key] = $value;
        }

        return true;
    }

    public function deleteMultiple($keys)
    {
        foreach ($keys as $key) {
            unset($this->data[$key]);
        }

        return true;
    }
}
