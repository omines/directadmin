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
use Omines\DirectAdmin\DirectAdminException;
use Omines\DirectAdmin\Objects\Users\Admin;
use Omines\DirectAdmin\Objects\Users\Reseller;
use Omines\DirectAdmin\Objects\Users\User;

/**
 * Context for user functions.
 *
 * @author Niels Keurentjes <niels.keurentjes@omines.com
 */
class UserContext extends BaseContext
{
    /** @var User */
    private $user;

    /** @var DomainContext[] */
    private $domains;

    /**
     * @param DirectAdmin $connection A prepared connection.
     * @param bool $validate Whether to check if the connection matches the context.
     */
    public function __construct(DirectAdmin $connection, $validate = false)
    {
        parent::__construct($connection);
        if($validate)
        {
            $classMap = [
                DirectAdmin::ACCOUNT_TYPE_ADMIN => AdminContext::class,
                DirectAdmin::ACCOUNT_TYPE_RESELLER => ResellerContext::class,
                DirectAdmin::ACCOUNT_TYPE_USER => UserContext::class,
            ];
            if($classMap[$this->getType()] != get_class($this))
                throw new DirectAdminException('Validation mismatch on context construction');
        }
    }

    /**
     * @return string One of the DirectAdmin::ACCOUNT_TYPE_ constants describing the type of underlying account.
     */
    public function getType()
    {
        return $this->getContextUser()->getType();
    }

    /**
     * @return Admin|Reseller|User The user object behind the context.
     */
    public function getContextUser()
    {
        if(!isset($this->user))
            $this->user = User::fromConfig($this->invokeGet('SHOW_USER_CONFIG'), $this);
        return $this->user;
    }

    /**
     * @param string $domainName
     * @return null|DomainContext
     */
    public function getDomain($domainName)
    {
        if(!isset($this->domains))
            $this->getDomains();
        return isset($this->domains[$domainName]) ? $this->domains[$domainName] : null;
    }

    /**
     * @return DomainContext[] Associative array of owned domains of this user.
     */
    public function getDomains()
    {
        if(!isset($this->domains))
        {
            $this->domains = $this->invokeGet('ADDITIONAL_DOMAINS');
            array_walk($this->domains, function(&$value) {
                $value = new DomainContext($this, $this->getConnection(), $value);
            });
        }
        return $this->domains;
    }

    /**
     * @return string Username for the current context.
     */
    public function getUsername()
    {
        return $this->getConnection()->getUsername();
    }
}
