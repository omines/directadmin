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
use Omines\DirectAdmin\Objects\Email\Forwarder;

/**
 * Encapsulates a domain and its derived objects, like aliases, pointers and mailboxes.
 *
 * @author Niels Keurentjes <niels.keurentjes@omines.com>
 */
class Domain extends Object
{
    /** @var string */
    private $domainName;

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
     * @param string $config The basic config string as returned by CMD_API_ADDITIONAL_DOMAINS.
     */
    public function __construct($name, UserContext $context, $config)
    {
        parent::__construct($name, $context);

        // Unpack domain config
        $data = \GuzzleHttp\Psr7\parse_query($config);
        $this->domainName = $data['domain'];

        $bandwidths = array_map('trim', explode('/', $data['bandwidth']));
        $this->bandwidthUsed = floatval($bandwidths[0]);
        $this->bandwidthLimit = ctype_alpha($bandwidths[1]) ? null : floatval($bandwidths[1]);
        $this->diskUsage = floatval($data['quota']);

        $this->aliases = array_filter(explode('|', $data['alias_pointers']));
        $this->pointers = array_filter(explode('|', $data['pointers']));
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
        return $this->getCache('forwarders', function() {
            $forwarders = $this->getContext()->invokeGet('EMAIL_FORWARDERS', ['domain' => $this->getDomainName()]);
            return DomainObject::toDomainObjectArray($forwarders, Forwarder::class, $this);
        });
    }

    public function getMailboxes()
    {
        return $this->getContext()->invokeGet('POP', ['domain' => $this->getDomainName(), 'action' => 'list']);
    }

    /**
     * @return string[] List of domain pointers for this domain.
     */
    public function getPointers()
    {
        return $this->pointers;
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
