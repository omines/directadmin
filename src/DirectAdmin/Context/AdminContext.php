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
use Omines\DirectAdmin\Objects\Users\Admin;
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
     * Creates a new Admin level account.
     *
     * @param string $username
     * @param string $password
     * @param string $email
     * @return Admin The newly created Admin.
     */
    public function createAdmin($username, $password, $email)
    {
        return $this->createAccount($username, $password, $email, [], 'ACCOUNT_ADMIN', Admin::class);
    }

    /**
     * @param string $username
     * @param string $password
     * @param string $email
     * @param string $domain
     * @param string|array $package Either a package name or an array of options for custom.
     * @param string $ip shared, sharedreseller or assign. Defaults to 'shared'.
     * @return mixed
     * @url http://www.directadmin.com/api.html#create for options to use.
     */
    public function createReseller($username, $password, $email, $domain, $package = [], $ip = 'shared')
    {
        $options = array_merge(
            ['ip' => $ip, 'domain' => $domain, 'serverip' => 'ON', 'dns' => 'OFF'],
            is_array($package) ? $package : ['package' => $package]
        );
        return $this->createAccount($username, $password, $email, $options, 'ACCOUNT_RESELLER', Reseller::class);
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
     * @param bool $validate Whether to check the reseller exists and is a reseller.
     * @return ResellerContext
     */
    public function impersonateReseller($username, $validate = false)
    {
        return new ResellerContext($this->getConnection()->loginAs($username), $validate);
    }
}
