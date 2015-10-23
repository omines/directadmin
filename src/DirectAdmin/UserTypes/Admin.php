<?php
/**
 * DirectAdmin
 * (c) Omines Internetbureau B.V.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Omines\DirectAdmin\UserTypes;
use Omines\DirectAdmin\DirectAdminException;

/**
 * Admin
 *
 * @author Niels Keurentjes <niels.keurentjes@omines.com
 */
class Admin extends Reseller
{
    public function createReseller($options = [])
    {
        self::checkMandatoryOptions($options, ['username', 'passwd', 'email', 'domain']);

        // Merge defaults and overrides
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

        return $this->invoke('POST', 'ACCOUNT_RESELLER', ['form_params' => $options]);
    }

    public function getResellers()
    {
        return $this->invokeGet('SHOW_RESELLERS');
    }
}
