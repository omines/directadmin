<?php
/**
 * DirectAdmin
 * (c) Omines Internetbureau B.V.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Omines\DirectAdmin\Context;

use Omines\DirectAdmin\DirectAdmin;
use Omines\DirectAdmin\Objects\Domain;

/**
 * DomainContext
 *
 * @author Niels Keurentjes <niels.keurentjes@omines.com
 */
class DomainContext extends BaseContext
{
    /** @var UserContext */
    private $userContext;

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
     * @param UserContext $userContext
     * @param DirectAdmin $connection
     * @param string $domainName
     * @param string $data
     */
    public function __construct(UserContext $userContext, DirectAdmin $connection, $data)
    {
        parent::__construct($connection);
        $this->userContext = $userContext;

        // Unpack domain data
        $data = \GuzzleHttp\Psr7\parse_query($data);
        $this->domainName = $data['domain'];

        $bandwidths = array_map('trim', explode('/', $data['bandwidth']));
        $this->bandwidthUsed = floatval($bandwidths[0]);
        $this->bandwidthLimit = ctype_alpha($bandwidths[1]) ? null : floatval($bandwidths[1]);
        $this->diskUsage = floatval($data['quota']);

        $this->aliases = array_filter(explode('|', $data['alias_pointers']));
        $this->pointers = array_filter(explode('|', $data['pointers']));
    }

    /**
     * @return array
     */
    public function getAliases()
    {
        return $this->aliases;
    }

    /**
     * @return array
     */
    public function getPointers()
    {
        return $this->pointers;
    }

    /**
     * @return float
     */
    public function getBandwidthUsed()
    {
        return $this->bandwidthUsed;
    }

    /**
     * @return float|null
     */
    public function getBandwidthLimit()
    {
        return $this->bandwidthLimit;
    }

    /**
     * @return float
     */
    public function getDiskUsage()
    {
        return $this->diskUsage;
    }

    /**
     * @return string
     */
    public function getDomainName()
    {
        return $this->domainName;
    }

    public function getEmailForwarders()
    {
        return $this->invokeGet('EMAIL_FORWARDERS', ['domain' => $this->getDomainName()]);
    }

    public function getMailboxes()
    {
        return $this->invokeGet('POP', ['domain' => $this->getDomainName(), 'action' => 'list']);
    }

    /**
     * @return UserContext
     */
    public function getUserContext()
    {
        return $this->userContext;
    }
}
