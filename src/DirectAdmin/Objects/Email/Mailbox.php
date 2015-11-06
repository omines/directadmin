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
 * Encapsulates a full mailbox with POP/IMAP/webmail access.
 *
 * @author Niels Keurentjes <niels.keurentjes@omines.com>
 */
class Mailbox extends MailObject
{
    const CACHE_DATA = 'mailbox';

    /**
     * Construct the object.
     *
     * @param string $name The domain name.
     * @param Domain $domain The containing domain.
     * @param string|array|null $config URL encoded config string as returned by CMD_API_POP.
     */
    public function __construct($name, Domain $domain, $config = null)
    {
        parent::__construct($name, $domain);
        if(isset($config))
            $this->setCache(self::CACHE_DATA, is_string($config) ? \GuzzleHttp\Psr7\parse_query($config) : $config);
    }

    /**
     * Creates a new mailbox.
     *
     * @param Domain $domain Domain to add the account to.
     * @param string $prefix Prefix for the account.
     * @param string $password Password for the account.
     * @param int|null $quota Quota in megabytes, or zero/null for unlimited.
     * @param int|null $sendLimit Send limit, or 0 for unlimited, or null for system default.
     * @return Mailbox The created mailbox.
     */
    public static function create(Domain $domain, $prefix, $password, $quota = null, $sendLimit = null)
    {
        $domain->getContext()->invokePost('POP', [
            'action' => 'create',
            'domain' => $domain->getDomainName(),
            'user' => $prefix,
            'passwd' => $password,
            'passwd2' => $password,
            'quota' => intval($quota) ?: 0,
            'limit' => isset($sendLimit) ? (intval($sendLimit) ?: 0) : null,
        ]);
        $domain->clearCache();
        return new self($prefix, $domain);
    }

    /**
     * Deletes the mailbox.
     */
    public function delete()
    {
        $this->getContext()->invokePost('POP', [
            'action' => 'delete',
            'domain' => $this->getDomainName(),
            'user' => $this->getPrefix(),
        ]);
        $this->clearDomainCache();
    }

    public function setPassword($newPassword)
    {
        $this->getContext()->invokePost('POP', [
            'action' => 'modify',
            'domain' => $this->getDomainName(),
            'user' => $this->getPrefix(),
            'passwd' => $newPassword,
            'passwd2' => $newPassword,
        ]);
    }

    /**
     * @return float|null
     */
    public function getDiskLimit()
    {
        return floatval($this->getData('quota')) ?: null;
    }

    /**
     * @return float
     */
    public function getDiskUsage()
    {
        return floatval($this->getData('usage'));
    }

    /**
     * @return int
     */
    public function getMailsSent()
    {
        return intval($this->getData('sent'));
    }

    /**
     * Cache wrapper to keep mailbox stats up to date.
     *
     * @param string $key
     * @return mixed
     */
    protected function getData($key)
    {
        return $this->getCacheItem(self::CACHE_DATA, $key, function() {
            $result = $this->getContext()->invokeGet('POP', [
                'domain' => $this->getDomainName(),
                'action' => 'full_list',
            ]);
            return \GuzzleHttp\Psr7\parse_query($result[$this->getPrefix()]);
        });
    }
}
