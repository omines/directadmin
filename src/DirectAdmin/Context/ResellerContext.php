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
 * @author Niels Keurentjes <niels.keurentjes@omines.com
 */
class ResellerContext extends UserContext
{
    /**
     * @param string $username User to delete.
     */
    public function deleteUser($username)
    {
        $this->invokePost('SELECT_USERS', [
            'confirmed' => 'Confirm',
            'delete'    => 'yes',
            'select0'   => $username,
        ]);
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
}
