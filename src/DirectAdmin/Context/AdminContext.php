<?php

/*
 * DirectAdmin API Client
 * (c) Omines Internetbureau B.V. - https://omines.nl/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Omines\DirectAdmin\Context;

use Omines\DirectAdmin\Objects\BaseObject;
use Omines\DirectAdmin\Objects\Users\Admin;
use Omines\DirectAdmin\Objects\Users\Reseller;
use Omines\DirectAdmin\Objects\Users\User;

/**
 * Context for administrator functions.
 *
 * @author Niels Keurentjes <niels.keurentjes@omines.com>
 */
class AdminContext extends ResellerContext
{
    /**
     * Creates a new Admin level account.
     *
     * @param string $username
     * @param string $password
     * @param string $email
     * @return Admin The newly created Admin
     */
    public function createAdmin($username, $password, $email)
    {
        return $this->createAccount($username, $password, $email, [], 'ACCOUNT_ADMIN', Admin::class);
    }

    /**
     * Creates a new Reseller level account.
     *
     * @param string $username
     * @param string $password
     * @param string $email
     * @param string $domain
     * @param string|array $package Either a package name or an array of options for custom
     * @param string $ip shared, sharedreseller or assign. Defaults to 'shared'
     * @return Reseller
     * @url http://www.directadmin.com/api.html#create for options to use.
     */
    public function createReseller($username, $password, $email, $domain, $package = [], $ip = 'shared')
    {
        $options = array_merge(
            ['ip' => $ip, 'domain' => $domain, 'serverip' => 'ON', 'dns' => 'OFF'],
            is_array($package) ? $package : ['package' => $package]
        );
        return $this->createAccount($username, $password, $email, $options, 'ACCOUNT_RESELLER', Reseller::class);
    }

    /**
     * Returns a list of known admins on the server.
     *
     * @return Admin[]
     */
    public function getAdmins()
    {
        return BaseObject::toObjectArray($this->invokeApiGet('SHOW_ADMINS'), Admin::class, $this);
    }

    /**
     * Returns a full list of all accounts of any type on the server.
     *
     * @return User[]
     */
    public function getAllAccounts()
    {
        $accounts = array_merge($this->getAllUsers(), $this->getResellers(), $this->getAdmins());
        ksort($accounts);
        return $accounts;
    }

    /**
     * Returns a full list of all users on the server, so no resellers or admins.
     *
     * @return User[]
     */
    public function getAllUsers()
    {
        return BaseObject::toObjectArray($this->invokeApiGet('SHOW_ALL_USERS'), User::class, $this);
    }

    /**
     * Returns a specific reseller by name, or NULL if there is no reseller by this name.
     *
     * @param string $username
     * @return null|Reseller
     */
    public function getReseller($username)
    {
        $resellers = $this->getResellers();
        return isset($resellers[$username]) ? $resellers[$username] : null;
    }

    /**
     * Returns the list of known resellers.
     *
     * @return Reseller[]
     */
    public function getResellers()
    {
        return BaseObject::toObjectArray($this->invokeApiGet('SHOW_RESELLERS'), Reseller::class, $this);
    }

    /**
     * Returns a new AdminContext acting as the specified admin.
     *
     * @param string $username
     * @param bool $validate Whether to check the admin exists and is an admin
     * @return AdminContext
     */
    public function impersonateAdmin($username, $validate = false)
    {
        return new self($this->getConnection()->loginAs($username), $validate);
    }

    /**
     * Returns a new ResellerContext acting as the specified reseller.
     *
     * @param string $username
     * @param bool $validate Whether to check the reseller exists and is a reseller
     * @return ResellerContext
     */
    public function impersonateReseller($username, $validate = false)
    {
        return new ResellerContext($this->getConnection()->loginAs($username), $validate);
    }
}
