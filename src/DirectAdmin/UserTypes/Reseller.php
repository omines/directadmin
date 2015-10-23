<?php
/**
 * DirectAdmin
 * (c) Omines Internetbureau B.V.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Omines\DirectAdmin\UserTypes;

/**
 * Reseller
 *
 * @author Niels Keurentjes <niels.keurentjes@omines.com
 */
class Reseller extends User
{
    public function getUsers()
    {
        return $this->invokeGet('SHOW_USERS');
    }

}
