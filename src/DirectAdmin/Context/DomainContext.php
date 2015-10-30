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
    public function getDomainName()
    {
        return $this->domainName;
    }

    /**
     * @return UserContext
     */
    public function getUserContext()
    {
        return $this->userContext;
    }
}