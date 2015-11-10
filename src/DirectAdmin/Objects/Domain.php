<?php
/**
 * DirectAdmin
 * (c) Omines Internetbureau B.V.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Omines\DirectAdmin\Objects;

use Omines\DirectAdmin\Context\ResellerContext;
use Omines\DirectAdmin\Context\UserContext;
use Omines\DirectAdmin\DirectAdminException;
use Omines\DirectAdmin\Objects\Domains\Subdomain;
use Omines\DirectAdmin\Objects\Email\Forwarder;
use Omines\DirectAdmin\Objects\Email\Mailbox;
use Omines\DirectAdmin\Objects\Users\User;
use Omines\DirectAdmin\Utility\Conversion;

/**
 * Encapsulates a domain and its derived objects, like aliases, pointers and mailboxes.
 *
 * @author Niels Keurentjes <niels.keurentjes@omines.com>
 */
class Domain extends Object
{
    const CACHE_FORWARDERS      = 'forwarders';
    const CACHE_MAILBOXES       = 'mailboxes';
    const CACHE_SUBDOMAINS      = 'subdomains';

    /** @var string */
    private $domainName;

    /** @var User */
    private $owner;

    /** @var string[] */
    private $aliases;

    /** @var string[] */
    private $pointers;

    /** @var float */
    private $bandwidthUsed;

    /** @var float|null */
    private $bandwidthLimit;

    /** @var float */
    private $diskUsage;

    /**
     * Construct the object.
     *
     * @param string $name The domain name.
     * @param UserContext $context The owning user context.
     * @param string|array $config The basic config string as returned by CMD_API_ADDITIONAL_DOMAINS.
     */
    public function __construct($name, UserContext $context, $config)
    {
        parent::__construct($name, $context);

        // Unpack domain config
        $data = is_array($config) ? $config : \GuzzleHttp\Psr7\parse_query($config);
        $this->domainName = $data['domain'];

        // Determine owner
        if($data['username'] === $context->getUsername())
            $this->owner = $context->getContextUser();
        else
            throw new DirectAdminException('Could not determine relationship between context user and domain');

        // Parse plain options
        $bandwidths = array_map('trim', explode('/', $data['bandwidth']));
        $this->bandwidthUsed = floatval($bandwidths[0]);
        $this->bandwidthLimit = ctype_alpha($bandwidths[1]) ? null : floatval($bandwidths[1]);
        $this->diskUsage = floatval($data['quota']);

        $this->aliases = array_filter(explode('|', $data['alias_pointers']));
        $this->pointers = array_filter(explode('|', $data['pointers']));
    }

    /**
     * Creates a new domain under the specified user.
     *
     * @param User $user Owner of the domain.
     * @param string $domainName Domain name to create.
     * @param float|null $bandwidthLimit Bandwidth limit in MB, or NULL to share with account.
     * @param float|null $diskLimit Disk limit in MB, or NULL to share with account.
     * @param bool|null $ssl Whether SSL is to be enabled, or NULL to fallback to account default.
     * @param bool|null $php Whether PHP is to be enabled, or NULL to fallback to account default.
     * @param bool|null $cgi Whether CGI is to be enabled, or NULL to fallback to account default.
     * @return Domain The newly created domain.
     */
    public static function create(User $user, $domainName, $bandwidthLimit = null, $diskLimit = null, $ssl = null, $php = null, $cgi = null)
    {
        $options = [
            'action' => 'create',
            'domain' => $domainName,
            (isset($bandwidthLimit) ? 'bandwidth' : 'ubandwidth') => $bandwidthLimit,
            (isset($diskLimit) ? 'quota' : 'uquota') => $diskLimit,
            'ssl' => Conversion::onOff($ssl, $user->hasSSL()),
            'php' => Conversion::onOff($php, $user->hasPHP()),
            'cgi' => Conversion::onOff($cgi, $user->hasCGI()),
        ];
        $user->getContext()->invokePost('DOMAIN', $options);
        $config = $user->getContext()->invokeGet('ADDITIONAL_DOMAINS');
        return new self($domainName, $user->getContext(), $config[$domainName]);
    }

    /**
     * Creates a new email forwarder.
     *
     * @param string $prefix Part of the email address before the @.
     * @param string|string[] $recipients One or more recipients.
     * @return Forwarder The newly created forwarder.
     */
    public function createForwarder($prefix, $recipients)
    {
        return Forwarder::create($this, $prefix, $recipients);
    }

    /**
     * Creates a new mailbox.
     *
     * @param string $prefix Prefix for the account
     * @param string $password Password for the account.
     * @param int|null $quota Quota in megabytes, or zero/null for unlimited.
     * @param int|null $sendLimit Send limit, or 0 for unlimited, or null for system default.
     * @return Mailbox The newly created mailbox.
     */
    public function createMailbox($prefix, $password, $quota = null, $sendLimit = null)
    {
        return Mailbox::create($this, $prefix, $password, $quota, $sendLimit);
    }

    /**
     * Creates a new subdomain.
     *
     * @param string $prefix Prefix to add before the domain name.
     * @return Subdomain The newly created subdomain.
     */
    public function createSubdomain($prefix)
    {
        return Subdomain::create($this, $prefix);
    }

    /**
     * Deletes this domain from the user.
     */
    public function delete()
    {
        $this->getContext()->invokePost('DOMAIN', [
            'delete' => true,
            'confirmed' => true,
            'select0' => $this->domainName,
        ]);
        $this->owner->clearCache();
    }

    /**
     * @return string[] List of aliases for this domain.
     */
    public function getAliases()
    {
        return $this->aliases;
    }

    /**
     * Returns unified sorted list of main domain name, aliases and pointers.
     *
     * @return string[]
     */
    public function getDomainNames()
    {
        return $this->getCache('domainNames', function() {
            $list = array_merge($this->aliases, $this->pointers, [$this->getDomainName()]);
            sort($list);
            return $list;
        });
    }

    /**
     * @return float Bandwidth used in megabytes.
     */
    public function getBandwidthUsed()
    {
        return $this->bandwidthUsed;
    }

    /**
     * @return float|null Bandwidth quotum in megabytes, or NULL for unlimited.
     */
    public function getBandwidthLimit()
    {
        return $this->bandwidthLimit;
    }

    /**
     * @return float Disk usage in megabytes.
     */
    public function getDiskUsage()
    {
        return $this->diskUsage;
    }

    /**
     * @return string The real domain name.
     */
    public function getDomainName()
    {
        return $this->domainName;
    }

    /**
     * @return Forwarder[] Associative array of forwarders.
     */
    public function getForwarders()
    {
        return $this->getCache(self::CACHE_FORWARDERS, function() {
            $forwarders = $this->getContext()->invokeGet('EMAIL_FORWARDERS', [
                'domain' => $this->getDomainName()
            ]);
            return DomainObject::toDomainObjectArray($forwarders, Forwarder::class, $this);
        });
    }

    /**
     * @return Mailbox[] Associative array of mailboxes.
     */
    public function getMailboxes()
    {
        return $this->getCache(self::CACHE_MAILBOXES, function() {
            $boxes = $this->getContext()->invokeGet('POP', [
                'domain' => $this->getDomainName(),
                'action' => 'full_list',
            ]);
            return DomainObject::toDomainObjectArray($boxes, Mailbox::class, $this);
        });
    }

    /**
     * @return User
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * @return Subdomain[] Associative array of subdomains.
     */
    public function getSubdomains()
    {
        return $this->getCache(self::CACHE_SUBDOMAINS, function() {
            $subs = $this->getContext()->invokeGet('SUBDOMAINS', ['domain' => $this->getDomainName()]);
            $subs = array_combine($subs, $subs);
            return DomainObject::toDomainObjectArray($subs, Subdomain::class, $this);
        });
    }

    /**
     * @return string[] List of domain pointers for this domain.
     */
    public function getPointers()
    {
        return $this->pointers;
    }

    /**
     * Invokes a POST command on a domain object.
     *
     * @param string $command Command to invoke.
     * @param string $action Action to execute.
     * @param array $parameters Additional options for the command.
     * @param bool $clearCache Whether to clear the domain cache on success.
     * @return array Response from the API.
     */
    public function invokePost($command, $action, $parameters = [], $clearCache = true)
    {
        $response = $this->getContext()->invokePost($command, array_merge([
            'action' => $action,
            'domain' => $this->domainName,
        ], $parameters));
        if($clearCache)
            $this->clearCache();
        return $response;
    }

    /**
     * Allows Domain object to be passed as a string with its domain name.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getDomainName();
    }
}
