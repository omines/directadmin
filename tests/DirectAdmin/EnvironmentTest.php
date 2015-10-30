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
 * Tests for responses to invalid environment configuration.
 *
 * @author Niels Keurentjes <niels.keurentjes@omines.com
 */
class EnvironmentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException Omines\DirectAdmin\DirectAdminException
     */
    public function testInvalidCredentials()
    {
        $admin = DirectAdmin::connectAdmin(DIRECTADMIN_URL, ADMIN_USERNAME, ADMIN_PASSWORD . 'invalid');
        $admin->getContextUser()->getType();
    }

    /**
     * @expectedException Omines\DirectAdmin\DirectAdminException
     */
    public function testInvalidUrl()
    {
        $admin = DirectAdmin::connectAdmin('http://www.example.com/', 'username', 'password');
        $admin->getContextUser()->getType();
    }


}
