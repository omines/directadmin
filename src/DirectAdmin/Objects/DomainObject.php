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
 * Encapsulates a domain-bound object.
 *
 * @author Niels Keurentjes <niels.keurentjes@omines.com>
 */
abstract class DomainObject extends Object
{
    /** @var Domain */
    private $domain;

    /**
     * @param string $name Canonical name for the object.
     * @param UserContext $context Context within which the object is valid.
     */
    protected function __construct($name, UserContext $context, Domain $domain)
    {
        parent::__construct($name, $context);
        $this->domain = $domain;
    }

    /**
     * @return Domain
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * Converts an associative array of descriptors to objects of the specified type.
     *
     * @param array $items
     * @param string $class
     * @param UserContext $context
     * @param Domain $domain
     * @return array
     */
    public static function toDomainObjectArray(array $items, $class, UserContext $context, Domain $domain)
    {
        array_walk($items, function(&$value, $name) use ($class, $context, $domain) {
            $value = new $class($name, $context, $domain, $value);
        });
        return $items;
    }
}
