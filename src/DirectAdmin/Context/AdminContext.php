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
use Omines\DirectAdmin\Objects\Users\Reseller;
use Omines\DirectAdmin\Objects\Users\User;

/**
 * Context for administrator functions.
 *
 * @author Niels Keurentjes <niels.keurentjes@omines.com
 */
class AdminContext extends ResellerContext
{
    /**
     * @param array $options Options to apply to the reseller.
     * @return Reseller Newly created reseller.
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

        $this->invokePost('ACCOUNT_RESELLER', $options);
        return new Reseller($options['username'], $this);
    }

    /**
     * @param string $username
     */
    public function deleteReseller($username)
    {
        $this->deleteUser($username);
    }

    /**
     * @return User[]
     */
    public function getAllUsers()
    {
        return Object::toObjectArray($this->invokeGet('SHOW_ALL_USERS'), User::class, $this);
    }

    /**
     * @param string $username
     * @return null|Reseller
     */
    public function getReseller($username)
    {
        $resellers = $this->getResellers();
        return isset($resellers[$username]) ? $resellers[$username] : null;
    }

    /**
     * @return Reseller[]
     */
    public function getResellers()
    {
        return Object::toObjectArray($this->invokeGet('SHOW_RESELLERS'), Reseller::class, $this);
    }

    /**
     * @param string $username
     * @return ResellerContext
     */
    public function impersonateReseller($username)
    {
        return new ResellerContext($this->getConnection()->loginAs($username));
    }
}
