<?php
/**
 * DirectAdmin
 * (c) Omines Internetbureau B.V.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Omines\DirectAdmin\Context\AdminContext;
use Omines\DirectAdmin\Context\DomainContext;
use Omines\DirectAdmin\DirectAdmin;
use Omines\DirectAdmin\DirectAdminException;
use Omines\DirectAdmin\Objects\Users\User;

/**
 * Unit tests for DirectAdmin wrapper class.
 *
 * @author Niels Keurentjes <niels.keurentjes@omines.com
 * @requires PHP 8
 */
class DirectAdminTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @inheritDoc
     */
    public static function setUpBeforeClass()
    {
        self::cleanupTestAccounts();
    }

    /**
     * @inheritDoc
     */
    public static function tearDownAfterClass()
    {
        self::cleanupTestAccounts();
    }

    private static function cleanupTestAccounts()
    {
        $context = DirectAdmin::connectAdmin(DIRECTADMIN_URL, MASTER_ADMIN_USERNAME, MASTER_ADMIN_PASSWORD);
        if($reseller = $context->getReseller(RESELLER_USERNAME))
        {
            if($user = $reseller->getUser(USER_USERNAME))
                $reseller->deleteUser(USER_USERNAME);
            $context->deleteReseller(RESELLER_USERNAME);
        }
    }

    public function testAdminLogin()
    {
        // Connect as admin and assure we have proper access
        $context = DirectAdmin::connectAdmin(DIRECTADMIN_URL, MASTER_ADMIN_USERNAME, MASTER_ADMIN_PASSWORD, true);
        $this->assertEquals(MASTER_ADMIN_USERNAME, $context->getUsername());
        $this->assertEquals(DirectAdmin::ACCOUNT_TYPE_ADMIN, $context->getType());
        return $context;
    }

    /**
     * @depends testAdminLogin
     */
    public function testCreateReseller(AdminContext $context)
    {
        // Create the reseller, and verify that afterwards there is 1 more reseller under the admin
        $before = count($context->getResellers());
        $reseller = $context->createReseller(RESELLER_USERNAME, RESELLER_PASSWORD, 'support@127.0.0.1', 'phpunit.example.com');
        $this->assertEquals($before + 1, count($context->getResellers()));

        // Impersonate to check things out
        $resellerContext = $reseller->impersonate();
        $this->assertEquals('phpunit.example.com', $resellerContext->getContextUser()->getDefaultDomain()->getDomainName());

        // Check for list of domains
        $this->assertCount(1, $domains = $resellerContext->getDomains());
        $this->assertNotEmpty($default = $reseller->getDefaultDomain());
        /** @var DomainContext $firstDomain */
        $firstDomain = reset($domains);
        $this->assertEquals($firstDomain->getDomainName(), $default->getDomainName());

        // Fetch some global stuff
        $allUsers = $context->getAllUsers();
        $this->assertEquals(RESELLER_USERNAME, $context->getReseller(RESELLER_USERNAME)->getUsername());

        // Double check that all stats are at sane defaults
        $firstDomain = $firstDomain->getDomain();
//        $this->assertEquals(0, $firstDomain->getBandwidthUsed());
//        $this->assertNull($firstDomain->getBandwidthLimit());
//        $this->assertEquals(0, $firstDomain->getStorageUsed());

        // Check that we can log in as the new reseller via all routes
        $resellerContext = DirectAdmin::connectReseller(DIRECTADMIN_URL, RESELLER_USERNAME, RESELLER_PASSWORD);
        $impersonated = $context->impersonateReseller(RESELLER_USERNAME);
        $this->assertEquals($resellerContext->getContextUser()->getDefaultDomain()->getDomainName(),
                            $impersonated->getContextUser()->getDefaultDomain()->getDomainName());

        // The reseller should have no users, either via context or directly
        $this->assertEmpty($impersonated->getUsers());
        $this->assertNull($impersonated->getUser('anyuser'));
        $this->assertEmpty($impersonated->getContextUser()->getUsers());
        $this->assertNull($impersonated->getContextUser()->getUser('anyuser'));

        // Manage the default domain a bit
        $this->assertArrayHasKey('phpunit.example.com', $impersonated->getDomains());
        $domain = $impersonated->getDomain('phpunit.example.com');
        $this->assertEquals($domain->getUserContext()->getUsername(), RESELLER_USERNAME);
        $this->assertEmpty($domain->getEmailForwarders());
        $this->assertEmpty($domain->getMailboxes());

        // HACK: Get IPs quick for this test
        $ips = $resellerContext->invokeGet('SHOW_RESELLER_IPS');

        // Create a user in the reseller
        $before = count($resellerContext->getUsers());
        $user = $resellerContext->createUser(USER_USERNAME, USER_PASSWORD, 'support@127.0.0.1', 'phpunit.example.org', $ips[0]);
        $this->assertEquals($before + 1, count($resellerContext->getUsers()));

        // Check that we can log in as the new user via all routes
        $userContext = DirectAdmin::connectUser(DIRECTADMIN_URL, USER_USERNAME, USER_PASSWORD);
        $impersonated = $context->impersonateUser(USER_USERNAME);
        $this->assertEquals($userContext->getContextUser()->getDefaultDomain()->getDomainName(),
                            $impersonated->getContextUser()->getDefaultDomain()->getDomainName());

        // Impersonate to check things out
        $userContext = $user->impersonate();
        $this->assertEquals('phpunit.example.org', $userContext->getContextUser()->getDefaultDomain()->getDomainName());

        // Clean up
        $resellerContext->deleteAccount(USER_USERNAME);
        $context->deleteAccount(RESELLER_USERNAME);

    }

    /**
     * @depends testAdminLogin
     * @expectedException Omines\DirectAdmin\DirectAdminException
     */
    public function testInvalidUser(AdminContext $context)
    {
        // Ensure an invalid execution throws a proper exception
        $this->setExpectedException(DirectAdminException::class);
        User::fromConfig(['username' => 'test', 'usertype' => 'test'], $context);
    }
}
