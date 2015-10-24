<?php
/**
 * DirectAdmin
 * (c) Omines Internetbureau B.V.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Omines\DirectAdmin\Context;

use Omines\DirectAdmin\DirectAdmin;
use Omines\DirectAdmin\Objects\User;

/**
 * User
 *
 * @author Niels Keurentjes <niels.keurentjes@omines.com
 */
class UserContext extends BaseContext
{
    private $user;

    /**
     * @param DirectAdmin $connection A prepared connection.
     * @param bool $validate Whether to check if the connection matches the context.
     */
    public function __construct(DirectAdmin $connection, $validate = false)
    {
        parent::__construct($connection);
    }

    public function getUser()
    {
        if(!isset($this->user))
            $this->user = User::fromConfig($this->invokeGet('SHOW_USER_CONFIG'), $this);
        return $this->user;
    }
}
