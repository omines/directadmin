<?php
/**
 * DirectAdmin
 * (c) Omines Internetbureau B.V.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Omines\DirectAdmin\Objects\Domains;

use Omines\DirectAdmin\Objects\Domain;
use Omines\DirectAdmin\Objects\DomainObject;

/**
 * Subdomain
 *
 * @author Niels Keurentjes <niels.keurentjes@omines.com>
 */
class Subdomain extends DomainObject
{
    /**
     * Construct the object.
     *
     * @param string $name The domain name.
     * @param Domain $domain The containing domain.
     */
    public function __construct($prefix, Domain $domain)
    {
        parent::__construct($prefix, $domain);
    }

    /**
     * Creates a new subdomain.
     *
     * @param Domain $domain Parent domain.
     * @param string $prefix Prefix of the subdomain.
     * @return Subdomain The newly created object.
     */
    public static function create(Domain $domain, $prefix)
    {
        $domain->getContext()->invokePost('SUBDOMAIN', [
            'action' => 'create',
            'domain' => $domain->getDomainName(),
            'subdomain' => $prefix,
        ]);
        $domain->clearCache();
        return new self($prefix, $domain);
    }

    /**
     * Deletes the subdomain.
     *
     * @param bool $deleteContents Whether to delete all directory contents as well.
     */
    public function delete($deleteContents = true)
    {
        $this->getContext()->invokePost('SUBDOMAIN', [
            'action' => 'delete',
            'domain' => $this->getDomainName(),
            'select0' => $this->getPrefix(),
            'contents' => ($deleteContents ? 'yes' : 'no')
        ]);
        $this->clearDomainCache();
    }

    /**
     * Returns the full domain name for the subdomain.
     *
     * @return string
     */
    public function getFullDomainName()
    {
        return $this->getPrefix() . '.' . $this->getDomainName();
    }

    /**
     * Returns the prefix of the subdomain.
     *
     * @return string
     */
    public function getPrefix()
    {
        return $this->getName();
    }

    /**
     * Allows the class to be used as a string representing the full domain name.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getFullDomainName();
    }
}
