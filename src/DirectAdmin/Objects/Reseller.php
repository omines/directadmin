<?php
/**
 * DirectAdmin
 * (c) Omines Internetbureau B.V.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Omines\DirectAdmin\Objects;

/**
 * Reseller
 *
 * @author Niels Keurentjes <niels.keurentjes@omines.com
 */
class Reseller extends User
{
    private $users;

    /**
     * @param $username
     * @return null|User
     */
    public function getUser($username)
    {
        $users = $this->getUsers();
        return isset($users[$username]) ? $users[$username] : null;
    }

    /**
     * @return User[]
     */
    public function getUsers()
    {
        if(!isset($this->users))
        {
            $users = $this->getContext()->invokeGet('SHOW_USERS');
            $this->users = array_combine($users, array_map(function($user) { return new User($user, $this->getContext()); }, $users));
        }
        return $this->users;
    }
}
