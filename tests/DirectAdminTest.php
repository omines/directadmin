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
 * Unit tests for DirectAdmin wrapper class.
 *
 * @author Niels Keurentjes <niels.keurentjes@omines.com
 */
class DirectAdminTest extends \PHPUnit_Framework_TestCase
{
    public function testDirectAdmin()
    {
        $admin = DirectAdmin::connectAdmin(DIRECTADMIN_URL, ADMIN_USERNAME, ADMIN_PASSWORD);
        $this->assertEquals('admin', $admin->getUserType());
    }
}
