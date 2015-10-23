<?php
/**
 * DirectAdmin
 * (c) Omines Internetbureau B.V.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Omines\DirectAdmin\DirectAdmin;
use Omines\DirectAdmin\DirectAdminException;

/**
 * Unit tests for DirectAdmin wrapper class.
 *
 * @author Niels Keurentjes <niels.keurentjes@omines.com
 */
class DirectAdminTest extends \PHPUnit_Framework_TestCase
{
    public function testDirectAdmin()
    {
        // Connect as admin and assure we have proper access
        $admin = DirectAdmin::connectAdmin(DIRECTADMIN_URL, ADMIN_USERNAME, ADMIN_PASSWORD);
        $this->assertEquals('admin', $admin->getUserType());

        // Clean up test users in case they got stuck after a failed unit test
        if(in_array(RESELLER_USERNAME, $admin->getResellers()))
        {
            $reseller = $admin->getReseller(RESELLER_USERNAME);
            if(in_array(USER_USERNAME, $reseller->getUsers()))
                $reseller->deleteUser(USER_USERNAME);
            $admin->deleteReseller(RESELLER_USERNAME);
        }

        // Create the reseller
        $admin->createReseller([
            'username' => RESELLER_USERNAME,
            'passwd' => substr(sha1(__FILE__ . time()), 0, 10),
            'email' => 'support@127.0.0.1',
            'domain' => 'phpunit.example.com',
        ]);

        // Ensure an invalid command throws a proper exception
        $this->setExpectedException(DirectAdminException::class);
        $admin->invokeGet('invalid_command');

    }
}
