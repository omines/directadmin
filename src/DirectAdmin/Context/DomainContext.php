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

    /**
     * @param UserContext $userContext
     * @param DirectAdmin $connection
     * @param string $domainName
     */
    public function __construct(UserContext $userContext, DirectAdmin $connection, $domainName)
    {
        parent::__construct($connection);
        $this->userContext = $userContext;
        $this->domainName = $domainName;
    }

    /**
     * @return string
     */
    public function getDomain()
    {
        return new Domain($this->domainName, $this->userContext);
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
