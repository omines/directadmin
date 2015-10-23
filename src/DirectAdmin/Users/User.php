<?php
/**
 * DirectAdmin
 * (c) Omines Internetbureau B.V.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Omines\DirectAdmin\Users;

use Omines\DirectAdmin\DirectAdmin;
use Omines\DirectAdmin\DirectAdminException;

/**
 * User
 *
 * @author Niels Keurentjes <niels.keurentjes@omines.com
 */
class User extends DirectAdmin
{
    public function getUserType()
    {
        return $this->getSessionInfo('usertype');
    }

    private function getSessionInfo($item)
    {
        if(!isset($this->session))
            $this->session = $this->invokeGet('CMD_API_SHOW_USER_CONFIG');
        if(!isset($this->session[$item]))
            throw new DirectAdminException("Session item '$item' does not exist");
        return $this->session[$item];
    }

    private $session;
}
