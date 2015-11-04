<?php
/**
 * DirectAdmin
 * (c) Omines Internetbureau B.V.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Omines\DirectAdmin\Context;

use Omines\DirectAdmin\Objects\Object;
use Omines\DirectAdmin\Objects\Users\User;

/**
 * Context for reseller functions.
 *
 * @author Niels Keurentjes <niels.keurentjes@omines.com>
 */
class ResellerContext extends UserContext
{
    /**
     * @param string $username
     * @param string $password
     * @param string $email
     * @param string $domain
     * @param string $ip IP for the user.
     * @param string|array $package Either a package name or an array of options for custom.
     * @return User Newly created user.
     * @url http://www.directadmin.com/api.html#create for options to use.
     */
    public function createUser($username, $password, $email, $domain, $ip, $package = [])
    {
        $options = array_merge(
            ['ip' => $ip, 'domain' => $domain],
            is_array($package) ? $package : ['package' => $package]
        );
        return $this->createAccount($username, $password, $email, $options, 'ACCOUNT_USER', User::class);
    }

    /**
     * Internal helper function for creating new accounts.
     *
     * @param string $username
     * @param string $password
     * @param string $email
     * @param array $options
     * @param string $endpoint
     * @param string $returnType
     * @return object An instance of the type specified in $returnType
     */
    protected function createAccount($username, $password, $email, $options, $endpoint, $returnType)
    {
        $this->invokePost($endpoint, array_merge($options, [
            'action' =>	'create',
            'add' => 'Submit',
            'email' => $email,
            'passwd' => $password,
            'passwd2' => $password,
            'username' => $username,
        ]));
        return new $returnType($username, $this);
    }

    /**
     * @param string $username Account to delete.
     */
    public function deleteAccount($username)
    {
        $this->deleteAccounts([$username]);
    }

    /**
     * @param string[] $usernames Accounts to delete.
     */
    public function deleteAccounts(array $usernames)
    {
        $options = ['confirmed' => 'Confirm', 'delete' => 'yes'];
        foreach(array_values($usernames) as $idx => $username)
            $options["select{$idx}"] = $username;
        $this->invokePost('SELECT_USERS', $options);
    }

    /**
     * @return array List of IPs owned by this reseller.
     */
    public function getIPs()
    {
        return $this->invokeGet('SHOW_RESELLER_IPS');
    }

    /**
     * @param string $username
     * @return User|null
     */
    public function getUser($username)
    {
        $resellers = $this->getUsers();
        return isset($resellers[$username]) ? $resellers[$username] : null;
    }

    /**
     * @return User[]
     */
    public function getUsers()
    {
        return Object::toObjectArray($this->invokeGet('SHOW_USERS'), User::class, $this);
    }

    /**
     * @param string $username
     * @param bool $validate Whether to check the user exists and is a user.
     * @return UserContext
     */
    public function impersonateUser($username, $validate = false)
    {
        return new UserContext($this->getConnection()->loginAs($username), $validate);
    }
}
