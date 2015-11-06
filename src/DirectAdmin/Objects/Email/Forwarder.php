<?php
/**
 * DirectAdmin
 * (c) Omines Internetbureau B.V.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Omines\DirectAdmin\Objects\Email;

use Omines\DirectAdmin\Objects\Domain;
use Omines\DirectAdmin\Objects\DomainObject;

/**
 * Encapsulates an email forwarder.
 *
 * @author Niels Keurentjes <niels.keurentjes@omines.com>
 */
class Forwarder extends DomainObject
{
    /** @var string[] */
    private $recipients;

    /**
     * Construct the object.
     *
     * @param string $name The domain name.
     * @param Domain $domain The containing domain.
     * @param array|string $recipients Array or string containing the recipients.
     */
    public function __construct($name, Domain $domain, $recipients)
    {
        parent::__construct($name, $domain);
        $this->recipients = is_string($recipients) ? array_map('trim', explode(',', $recipients)) : $recipients;
    }

    /**
     * @param Domain $domain
     * @param string $prefix
     * @param string|string[] $recipients
     * @return Forwarder
     */
    public static function create(Domain $domain, $prefix, $recipients)
    {
        $domain->getContext()->invokePost('EMAIL_FORWARDERS', [
            'action' => 'create',
            'domain' => $domain->getDomainName(),
            'user' => $prefix,
            'email' => is_array($recipients) ? implode(',', $recipients) : $recipients,
        ]);
        $domain->clearCache();
        return new Forwarder($prefix, $domain, $recipients);
    }

    /**
     * Deletes the forwarder.
     */
    public function delete()
    {
        $this->getContext()->invokePost('EMAIL_FORWARDERS', [
            'action' => 'delete',
            'domain' => $this->getDomainName(),
            'select0' => $this->getPrefix(),
        ]);
        $this->clearDomainCache();
    }

    /**
     * Returns the domain-agnostic part before the @ in the forwarder.
     *
     * @return string
     */
    public function getPrefix()
    {
        return $this->getName();
    }

    /**
     * @return string[]
     */
    public function getRecipients()
    {
        return $this->recipients;
    }

    /**
     * Returns the domain-agnostic part before the @ in the forwarder.
     *
     * @return string
     */
    public function getAliases()
    {
        return array_map(function($domain) {
            return $this->getPrefix() . '@' . $domain;
        }, $this->getDomain()->getDomainNames());
    }
}
