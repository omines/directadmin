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
     * @param array $options Options to apply to the user.
     * @return User Newly created user.
     * @url http://www.directadmin.com/api.html#create for options to use.
     */
    public function createUser($options = [])
    {
        // Check mandatory options
        self::checkMandatoryOptions($options, ['username', 'passwd', 'email', 'domain', 'ip']);
        return $this->createAccount($options, 'ACCOUNT_USER', User::class);
    }

    protected function createAccount($options, $endpoint, $returnType, $defaults = [])
    {
        // Merge defaults and overrides
        $options = array_merge([
            'dns' => 'OFF',
            'serverip' => 'ON',
        ], $defaults, $options, [
            'action' =>	'create',
            'add' => 'Submit',
        ]);
        if(!isset($options['passwd2']))
            $options['passwd2'] = $options['passwd'];

        $this->invokePost($endpoint, $options);
        return new $returnType($options['username'], $this);
    }

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

    /**
     * @param string $username
     * @return UserContext
     */
    public function impersonateUser($username)
    {
        return new UserContext($this->getConnection()->loginAs($username));
    }
}
