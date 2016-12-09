<?php

/*
 * DirectAdmin API Client
 * (c) Omines Internetbureau B.V. - https://omines.nl/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Omines\DirectAdmin\Objects;

use Omines\DirectAdmin\Context\UserContext;
use Omines\DirectAdmin\DirectAdminException;

/**
 * Basic wrapper around a DirectAdmin object as observed within a specific context.
 *
 * @author Niels Keurentjes <niels.keurentjes@omines.com>
 */
abstract class BaseObject
{
    /** @var string */
    private $name;

    /** @var UserContext */
    private $context;

    /** @var array */
    private $cache = [];

    /**
     * @param string $name Canonical name for the object
     * @param UserContext $context Context within which the object is valid
     */
    protected function __construct($name, UserContext $context)
    {
        $this->name = $name;
        $this->context = $context;
    }

    /**
     * Clear the object's internal cache.
     */
    public function clearCache()
    {
        $this->cache = [];
    }

    /**
     * Retrieves an item from the internal cache.
     *
     * @param string $key Key to retrieve
     * @param callable|mixed $default Either a callback or an explicit default value
     * @return mixed Cached value
     */
    protected function getCache($key, $default)
    {
        if (!isset($this->cache[$key])) {
            $this->cache[$key] = is_callable($default) ? $default() : $default;
        }
        return $this->cache[$key];
    }

    /**
     * Retrieves a keyed item from inside a cache item.
     *
     * @param string $key
     * @param string $item
     * @param callable|mixed $defaultKey
     * @param mixed|null $defaultItem
     * @return mixed Cached value
     *
     * @codeCoverageIgnore
     */
    protected function getCacheItem($key, $item, $defaultKey, $defaultItem = null)
    {
        if (empty($cache = $this->getCache($key, $defaultKey))) {
            return $defaultItem;
        }
        if (!is_array($cache)) {
            throw new DirectAdminException("Cache item $key is not an array");
        }
        return isset($cache[$item]) ? $cache[$item] : $defaultItem;
    }

    /**
     * Sets a specific cache item, for when a cacheable value was a by-product.
     *
     * @param string $key
     * @param mixed $value
     */
    protected function setCache($key, $value)
    {
        $this->cache[$key] = $value;
    }

    /**
     * @return UserContext
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * Protected as a derived class may want to offer the name under a different name.
     *
     * @return string
     */
    protected function getName()
    {
        return $this->name;
    }

    /**
     * Converts an array of string items to an associative array of objects of the specified type.
     *
     * @param array $items
     * @param string $class
     * @param UserContext $context
     * @return array
     */
    public static function toObjectArray(array $items, $class, UserContext $context)
    {
        return array_combine($items, array_map(function ($item) use ($class, $context) {
            return new $class($item, $context);
        }, $items));
    }

    /**
     * Converts an associative array of descriptors to objects of the specified type.
     *
     * @param array $items
     * @param string $class
     * @param UserContext $context
     * @return array
     */
    public static function toRichObjectArray(array $items, $class, UserContext $context)
    {
        array_walk($items, function (&$value, $name) use ($class, $context) {
            $value = new $class($name, $context, $value);
        });
        return $items;
    }
}
