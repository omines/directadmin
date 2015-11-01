<?php
/**
 * DirectAdmin
 * (c) Omines Internetbureau B.V.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Omines\DirectAdmin\DirectAdmin;

/**
 * Tests admin level functionality
 *
 * @author Niels Keurentjes <niels.keurentjes@omines.com
 */
class AdminTest extends \PHPUnit_Framework_TestCase
{
    public function testAccountListings()
    {
        $context = DirectAdmin::connectAdmin(DIRECTADMIN_URL, MASTER_ADMIN_USERNAME, MASTER_ADMIN_PASSWORD);
        $users = $context->getAllUsers();
        $resellers = $context->getResellers();
        $admins = $context->getAdmins();
        $account = $context->getAllAccounts();
        $this->assertEquals(count($account), count($users) + count($resellers) + count($admins));
    }

}
