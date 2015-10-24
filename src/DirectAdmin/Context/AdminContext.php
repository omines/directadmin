<?php
/**
 * DirectAdmin
 * (c) Omines Internetbureau B.V.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Omines\DirectAdmin\Context;
use Omines\DirectAdmin\Objects\Reseller;
use Omines\DirectAdmin\Objects\User;

/**
 * Context for administrator functions.
 *
 * @author Niels Keurentjes <niels.keurentjes@omines.com
 */
class AdminContext extends ResellerContext
{
    /**
     * @param array $options Options to apply to the reseller.
     * @return array
     * @url http://www.directadmin.com/api.html#create for options to use.
     */
    public function createReseller($options = [])
    {
        // Check mandatory options, then merge defaults and overrides
        self::checkMandatoryOptions($options, ['username', 'passwd', 'email', 'domain']);
        $options = array_merge([
            'dns' => 'OFF',
            'serverip' => 'ON',
            'ip' => 'shared',
        ], $options, [
            'action' =>	'create',
            'add' => 'Submit',
        ]);
        if(!isset($options['passwd2']))
            $options['passwd2'] = $options['passwd'];

        return $this->invokePost('ACCOUNT_RESELLER', $options);
    }

    /**
     * @param string $username
     */
    public function deleteReseller($username)
    {
        return $this->deleteUser($username);
    }

    /**
     * @return User[]
     */
    public function getAllUsers()
    {
        $users = $this->invokeGet('SHOW_ALL_USERS');
        return array_combine($users, array_map(function($user) { return new User($user, $this); }, $users));
    }

    /**
     * @param string $username
     * @return null|Reseller
     */
    public function getReseller($username)
    {
        return $this->getUser()->getReseller($username);
    }

    /**
     * @return Reseller[]
     */
    public function getResellers()
    {
        return $this->getUser()->getResellers();
    }
}
