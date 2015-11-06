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

/**
 * Encapsulates an email forwarder.
 *
 * @author Niels Keurentjes <niels.keurentjes@omines.com>
 */
class Forwarder extends MailObject
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
     * Creates a new forwarder.
     *
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
        return new self($prefix, $domain, $recipients);
    }

    /**
     * Deletes the forwarder.
     */
    public function delete()
    {
        parent::invokeDelete('EMAIL_FORWARDERS', 'select0');
    }

    /**
     * @return string[]
     */
    public function getRecipients()
    {
        return $this->recipients;
    }

    /**
     * Returns the list of valid aliases for this account.
     *
     * @return string[]
     */
    public function getAliases()
    {
        return array_map(function($domain) {
            return $this->getPrefix() . '@' . $domain;
        }, $this->getDomain()->getDomainNames());
    }
}
