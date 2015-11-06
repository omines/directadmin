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
     * @param Domain $domain Domain to which the object belongs.
     */
    protected function __construct($name, Domain $domain)
    {
        parent::__construct($name, $domain->getContext());
        $this->domain = $domain;
    }

    /**
     * Clears the domain's cache.
     */
    protected function clearDomainCache()
    {
        $this->domain->clearCache();
    }

    /**
     * @return Domain
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * @return string
     */
    public function getDomainName()
    {
        return $this->domain->getDomainName();
    }

    /**
     * Converts an associative array of descriptors to objects of the specified type.
     *
     * @param array $items
     * @param string $class
     * @param Domain $domain
     * @return array
     */
    public static function toDomainObjectArray(array $items, $class, Domain $domain)
    {
        array_walk($items, function(&$value, $name) use ($class, $domain) {
            $value = new $class($name, $domain, $value);
        });
        return $items;
    }
}
