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
use Omines\DirectAdmin\Objects\Users\User;

/**
 * Context for reseller functions.
 *
 * @author Niels Keurentjes <niels.keurentjes@omines.com>
 */
class ResellerContext extends UserContext
{
    /**
     * Creates a new user on the server.
     *
     * @param string $username Login for the new user
     * @param string $password Password for the new user
     * @param string $email Email for the new user
     * @param string $domain Default domain for the new user
     * @param string $ip IP for the user
     * @param string|array $package Either a package name or an array of options for custom
     * @return User Newly created user
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
     * @param string $username Login for the new user
     * @param string $password Password for the new user
     * @param string $email Email for the new user
     * @param array $options List of DA account options to apply
     * @param string $endpoint API endpoint to invoke
     * @param string $returnType Class name that should wrap the resulting account
     * @return object An instance of the type specified in $returnType
     */
    protected function createAccount($username, $password, $email, $options, $endpoint, $returnType)
    {
        $this->invokeApiPost($endpoint, array_merge($options, [
            'action' => 'create',
            'add' => 'Submit',
            'email' => $email,
            'passwd' => $password,
            'passwd2' => $password,
            'username' => $username,
        ]));
        return new $returnType($username, $this);
    }

    /**
     * Deletes a single account.
     *
     * @param string $username Account to delete
     */
    public function deleteAccount($username)
    {
        $this->deleteAccounts([$username]);
    }

    /**
     * Deletes multiple accounts.
     *
     * @param string[] $usernames Accounts to delete
     */
    public function deleteAccounts(array $usernames)
    {
        $options = ['confirmed' => 'Confirm', 'delete' => 'yes'];
        foreach (array_values($usernames) as $idx => $username) {
            $options["select{$idx}"] = $username;
        }
        $this->invokeApiPost('SELECT_USERS', $options);
    }

    /**
     * Suspends a single account.
     *
     * @param string $username Account to delete
     */
    public function suspendAccount($username)
    {
        $this->suspendAccounts([$username]);
    }

    /**
     * Unsuspends a single account.
     *
     * @param string $username Account to delete
     */
    public function unsuspendAccount($username)
    {
        $this->suspendAccounts([$username], false);
    }

    /**
     * Suspends (or unsuspends) multiple accounts.
     *
     * @param string[] $usernames Accounts to delete
     * @param bool $suspend (true - suspend, false - unsuspend)
     */
    public function suspendAccounts(array $usernames, $suspend = true)
    {
        $options = ['suspend' => $suspend ? 'Suspend' : 'Unsuspend'];
        foreach (array_values($usernames) as $idx => $username) {
            $options['select' . $idx] = $username;
        }
        $this->invokeApiPost('SELECT_USERS', $options);
    }

    /**
     * Unsuspends multiple accounts.
     *
     * @param string[] $usernames Accounts to delete
     */
    public function unsuspendAccounts(array $usernames)
    {
        $this->suspendAccounts($usernames, false);
    }

    /**
     * Returns all IPs available to this reseller.
     *
     * @return array List of IPs as strings
     */
    public function getIPs()
    {
        return $this->invokeApiGet('SHOW_RESELLER_IPS');
    }

    /**
     * Returns a single user by name.
     *
     * @param string $username
     * @return User|null
     */
    public function getUser($username)
    {
        $resellers = $this->getUsers();
        return isset($resellers[$username]) ? $resellers[$username] : null;
    }

    /**
     * Returns all users for this reseller.
     *
     * @return User[] Associative array of users
     */
    public function getUsers()
    {
        return BaseObject::toObjectArray($this->invokeApiGet('SHOW_USERS'), User::class, $this);
    }

    /**
     * Impersonates a user, allowing the reseller/admin to act on their behalf.
     *
     * @param string $username Login of the account to impersonate
     * @param bool $validate Whether to check the user exists and is a user
     * @return UserContext
     */
    public function impersonateUser($username, $validate = false)
    {
        return new UserContext($this->getConnection()->loginAs($username), $validate);
    }
}
