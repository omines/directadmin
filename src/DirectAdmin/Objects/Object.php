<?php
/**
 * DirectAdmin
 * (c) Omines Internetbureau B.V.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Omines\DirectAdmin\Objects;

use Omines\DirectAdmin\Context\UserContext;

/**
 * Basic wrapper around a DirectAdmin object as observed within a specific context.
 *
 * @author Niels Keurentjes <niels.keurentjes@omines.com>
 */
abstract class Object
{
    /** @var string */
    private $name;

    /** @var UserContext */
    private $context;

    /**
     * @param string $name Canonical name for the object.
     * @param UserContext $context Context within which the object is valid.
     */
    protected function __construct($name, UserContext $context)
    {
        $this->name = $name;
        $this->context = $context;
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
        return array_combine($items, array_map(function($item) use ($class, $context) {
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
        array_walk($items, function(&$value, $name) use ($class, $context) {
            $value = new $class($name, $context, $value);
        });
        return $items;
    }
}
