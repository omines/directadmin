<?php
/**
 * DirectAdmin
 * (c) Omines Internetbureau B.V.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Omines\DirectAdmin\Context;
use Omines\DirectAdmin\Objects\User;

/**
 * Context for reseller functions.
 *
 * @author Niels Keurentjes <niels.keurentjes@omines.com
 */
class ResellerContext extends UserContext
{
    public function deleteUser($username)
    {
        return $this->invokePost('SELECT_USERS', [
            'confirmed' => 'Confirm',
            'delete'    => 'yes',
            'select0'   => $username,
        ]);
    }

    /**
     * @param User|string $user
     * @return array
     */
    public function getDomains($user = null)
    {
        if(!isset($user))
            $user = $this->getUsername();
        elseif($user instanceof User)
            $user = $user->getUsername();
        return $this->invokeGet('SHOW_USER_DOMAINS', ['user' => $user]);
    }

    /**
     * @return User[] List of users for this reseller.
     */
    public function getUsers()
    {
        return $this->getUser()->getUsers();
    }
}
