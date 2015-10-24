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
 * Admin
 *
 * @author Niels Keurentjes <niels.keurentjes@omines.com
 */
class AdminContext extends ResellerContext
{
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

    public function deleteReseller($username)
    {
        return $this->deleteUser($username);
    }

    public function getReseller($username)
    {
        return $this->getUser()->getReseller($username);
    }

    public function getResellers()
    {
        return $this->getUser()->getResellers();
    }
}
