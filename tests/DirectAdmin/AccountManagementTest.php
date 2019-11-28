<?php

/*
 * DirectAdmin API Client
 * (c) Omines Internetbureau B.V. - https://omines.nl/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Omines\DirectAdmin\DirectAdmin;

/**
 * AccountManagementTest.
 *
 * @author Niels Keurentjes <niels.keurentjes@omines.com>
 */
class AccountManagementTest extends \PHPUnit\Framework\TestCase
{
    /**
     * This function is explicitly implemented as setup, not teardown, so in case of failed tests you may investigate
     * the accounts in DirectAdmin to see what's wrong.
     */
    public static function setUpBeforeClass()
    {
        try {
            // Ensure all test accounts are gone
            $adminContext = DirectAdmin::connectAdmin(DIRECTADMIN_URL, MASTER_ADMIN_USERNAME, MASTER_ADMIN_PASSWORD);
            $adminContext->deleteAccounts([USER_USERNAME, RESELLER_USERNAME, ADMIN_USERNAME]);

        } catch (\Exception $e) {
            // Silently fail as this is expected behaviour
        }
    }

    public function testCreateAdmin()
    {
        $adminContext = DirectAdmin::connectAdmin(DIRECTADMIN_URL, MASTER_ADMIN_USERNAME, MASTER_ADMIN_PASSWORD);
        $admin = $adminContext->createAdmin(ADMIN_USERNAME, ADMIN_PASSWORD, TEST_EMAIL);
        $this->assertEquals(ADMIN_USERNAME, $admin->getUsername());
        $this->assertEquals(DirectAdmin::ACCOUNT_TYPE_ADMIN, $admin->getType());
    }

    /**
     * @depends testCreateAdmin
     */
    public function testCreateReseller()
    {
        $adminContext = DirectAdmin::connectAdmin(DIRECTADMIN_URL, ADMIN_USERNAME, ADMIN_PASSWORD, true);
        $reseller = $adminContext->createReseller(RESELLER_USERNAME, RESELLER_PASSWORD,
                        TEST_EMAIL, TEST_RESELLER_DOMAIN);

        $this->assertEquals(RESELLER_USERNAME, $reseller->getUsername());
        $this->assertEquals(DirectAdmin::ACCOUNT_TYPE_RESELLER, $reseller->getType());
        $this->assertEquals($reseller->getDefaultDomain()->getDomainName(), TEST_RESELLER_DOMAIN);

        $getReseller = $adminContext->getReseller(RESELLER_USERNAME);
        $this->assertEquals(RESELLER_USERNAME, $getReseller->getUsername());
        $this->assertEquals(DirectAdmin::ACCOUNT_TYPE_RESELLER, $getReseller->getType());
    }

    /**
     * @depends testCreateReseller
     */
    public function testCreateUser()
    {
        $resellerContext = DirectAdmin::connectReseller(DIRECTADMIN_URL, RESELLER_USERNAME, RESELLER_PASSWORD, true);
        $this->assertNotEmpty($ips = $resellerContext->getIPs());

        $user = $resellerContext->createUser(USER_USERNAME, USER_PASSWORD,
                        TEST_EMAIL, TEST_USER_DOMAIN, $ips[0]);

        $this->assertEquals(USER_USERNAME, $user->getUsername());
        $this->assertEquals(DirectAdmin::ACCOUNT_TYPE_USER, $user->getType());
        $this->assertEquals($user->getDefaultDomain()->getDomainName(), TEST_USER_DOMAIN);
    }

    /**
     * @depends testCreateUser
     */
    public function testLoginUser()
    {
        $userContext = DirectAdmin::connectUser(DIRECTADMIN_URL, USER_USERNAME, USER_PASSWORD, true);
        $this->assertEquals(USER_USERNAME, $userContext->getUsername());
        $this->assertEquals(USER_USERNAME, $userContext->getContextUser()->getUsername());
    }

    /**
     * @depends testCreateUser
     */
    public function testImpersonation()
    {
        $adminContext = DirectAdmin::connectAdmin(DIRECTADMIN_URL, ADMIN_USERNAME, ADMIN_PASSWORD, true);
        $resellerContext = $adminContext->impersonateReseller(RESELLER_USERNAME);
        $userContext = $resellerContext->impersonateUser(USER_USERNAME);
        $this->assertEquals(DirectAdmin::ACCOUNT_TYPE_USER, $userContext->getType());
        $this->assertEquals(USER_USERNAME, $userContext->getContextUser()->getUsername());
    }

    /**
     * @depends testCreateUser
     */
    public function testSuspendAccounts()
    {
        // Have to separately suspend the user as otherwise the order is not determined whether it's containing
        // reseller is suspended first. Also - it implicitly tests both calls like this
        $adminContext = DirectAdmin::connectAdmin(DIRECTADMIN_URL, MASTER_ADMIN_USERNAME, MASTER_ADMIN_PASSWORD);
        $adminContext->suspendAccount(USER_USERNAME);
        $adminContext->suspendAccounts([RESELLER_USERNAME, ADMIN_USERNAME]);

        $user = $adminContext->impersonateUser( USER_USERNAME, true );
        $this->assertEquals( $user->getContextUser()->isSuspended(), true );

        $reseller = $adminContext->impersonateReseller( RESELLER_USERNAME, true );
        $this->assertEquals( $reseller->getContextUser()->isSuspended(), true );

        $admin = $adminContext->impersonateAdmin( ADMIN_USERNAME, true );
        $this->assertEquals( $admin->getContextUser()->isSuspended(), true );
    }

    /**
     * @depends testCreateUser
     */
    public function testUnsuspendAccounts()
    {
        $adminContext = DirectAdmin::connectAdmin(DIRECTADMIN_URL, MASTER_ADMIN_USERNAME, MASTER_ADMIN_PASSWORD);
        $adminContext->unsuspendAccount(USER_USERNAME);
        $adminContext->unsuspendAccounts([RESELLER_USERNAME, ADMIN_USERNAME]);

        $user = $adminContext->impersonateUser(USER_USERNAME, true);
        $this->assertEquals($user->getContextUser()->isSuspended(), false);

        $reseller = $adminContext->impersonateReseller(RESELLER_USERNAME, true);
        $this->assertEquals($reseller->getContextUser()->isSuspended(), false);

        $admin = $adminContext->impersonateAdmin(ADMIN_USERNAME, true);
        $this->assertEquals($admin->getContextUser()->isSuspended(), false);
    }

    /**
     * @depends testCreateUser
     */
    public function testDeleteAccounts()
    {
        // Have to separately delete the user as otherwise the order is not determined whether it's containing
        // reseller is removed first. Also - it implicitly tests both calls like this
        $adminContext = DirectAdmin::connectAdmin(DIRECTADMIN_URL, MASTER_ADMIN_USERNAME, MASTER_ADMIN_PASSWORD);
        $adminContext->deleteAccount(USER_USERNAME);
        $adminContext->deleteAccounts([RESELLER_USERNAME, ADMIN_USERNAME]);

        $this->assertNull( $adminContext->getUser( USER_USERNAME ) );
        $this->assertNull( $adminContext->getReseller( RESELLER_USERNAME ) );

        $admins = $adminContext->getAdmins();
        $this->assertFalse( in_array( ADMIN_USERNAME, $admins ) );
    }
}
