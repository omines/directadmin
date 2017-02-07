<?php

/*
 * DirectAdmin API Client
 * (c) Omines Internetbureau B.V. - https://omines.nl/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Omines\DirectAdmin\Context\AdminContext;
use Omines\DirectAdmin\DirectAdmin;
use Omines\DirectAdmin\Objects\Users\Admin;

/**
 * Tests admin level functionality.
 *
 * @author Niels Keurentjes <niels.keurentjes@omines.com>
 */
class AdminTest extends \PHPUnit\Framework\TestCase
{
    /** @var AdminContext */
    private static $master;

    /** @var Admin */
    private static $admin;

    public static function setUpBeforeClass()
    {
        self::$master = DirectAdmin::connectAdmin(DIRECTADMIN_URL, MASTER_ADMIN_USERNAME, MASTER_ADMIN_PASSWORD);
        self::$admin = self::$master->createAdmin(ADMIN_USERNAME, ADMIN_PASSWORD, TEST_EMAIL);
    }

    public static function tearDownAfterClass()
    {
        self::$master->deleteAccount(self::$admin->getUsername());
    }

    public function testImpersonate()
    {
        $context = self::$admin->impersonate();
        $this->assertEquals(self::$admin->getUsername(), $context->getUsername());
        return $context;
    }

    /**
     * @depends testImpersonate
     */
    public function testAccountListings(AdminContext $context)
    {
        $users = $context->getAllUsers();
        $resellers = $context->getResellers();
        $admins = $context->getAdmins();
        $accounts = $context->getAllAccounts();

        $this->assertArrayHasKey(ADMIN_USERNAME, $admins);
        $this->assertArrayHasKey(MASTER_ADMIN_USERNAME, $accounts);
        $this->assertEquals(count($accounts), count($users) + count($resellers) + count($admins));
    }
}
