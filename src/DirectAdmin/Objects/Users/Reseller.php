<?php
/**
 * DirectAdmin
 * (c) Omines Internetbureau B.V.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Omines\DirectAdmin\Objects\Users;
use Omines\DirectAdmin\Objects\Object;

/**
 * Reseller
 *
 * @author Niels Keurentjes <niels.keurentjes@omines.com
 */
class Reseller extends User
{
    /**
     * @param string $username
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
        return Object::toObjectArray($this->getContext()->invokeGet('SHOW_RESELLERS'), User::class, $this->getContext());
    }
}
