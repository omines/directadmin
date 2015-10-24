<?php
/**
 * DirectAdmin
 * (c) Omines Internetbureau B.V.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Omines\DirectAdmin\Context;

/**
 * Reseller
 *
 * @author Niels Keurentjes <niels.keurentjes@omines.com
 */
class ResellerContext extends UserContext
{
    public function deleteUser($username)
    {
        $this->invokePost('SELECT_USERS', [
            'confirmed' => 'Confirm',
            'delete'    => 'yes',
            'select0'   => $username,
        ]);
    }

    public function getUsers()
    {
        return $this->invokeGet('SHOW_USERS');
    }
}
