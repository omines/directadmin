<?php
/**
 * DirectAdmin
 * (c) Omines Internetbureau B.V.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Omines\DirectAdmin\Context\AdminContext;
use Omines\DirectAdmin\DirectAdmin;
use Omines\DirectAdmin\DirectAdminException;
use Omines\DirectAdmin\Objects\User;

/**
 * Unit tests for DirectAdmin wrapper class.
 *
 * @author Niels Keurentjes <niels.keurentjes@omines.com
 */
class DirectAdminTest extends \PHPUnit_Framework_TestCase
{
    public function testAdminLogin()
    {
        // Connect as admin and assure we have proper access
        $admin = DirectAdmin::connectAdmin(DIRECTADMIN_URL, ADMIN_USERNAME, ADMIN_PASSWORD);
        $this->assertEquals(DirectAdmin::USERTYPE_ADMIN, $admin->getUser()->getType());
        return $admin;
    }

    /**
     * @depends testAdminLogin
     */
    public function testCreateReseller(AdminContext $admin)
    {
        // Clean up test users first in case they got stuck after a failed unit test
        $this->cleanupTestAccounts($admin);

        // Create the reseller
        $before = count($admin->getResellers());
        $admin->createReseller([
            'username' => RESELLER_USERNAME,
            'passwd' => substr(sha1(__FILE__ . time()), 0, 10),
            'email' => 'support@127.0.0.1',
            'domain' => 'phpunit.example.com',
        ]);
        $this->assertEquals($before + 1, count($admin->getResellers()));
    }

    /**
     * @depends testAdminLogin
     * @expectedException Omines\DirectAdmin\DirectAdminException
     */
    public function testInvalidUser(AdminContext $admin)
    {
        // Ensure an invalid execution throws a proper exception
        $this->setExpectedException(DirectAdminException::class);
        $user = User::fromConfig(['username' => 'test', 'usertype' => 'test'], $admin);
    }

    private function cleanupTestAccounts(AdminContext $admin)
    {
        if($reseller = $admin->getReseller(RESELLER_USERNAME))
        {
            if($user = $reseller->getUser(USER_USERNAME))
                $reseller->deleteUser(USER_USERNAME);
            $admin->deleteReseller(RESELLER_USERNAME);
        }
    }
}
