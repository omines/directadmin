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
use Omines\DirectAdmin\Objects\Domain;
use Omines\DirectAdmin\Objects\Users\User;

/**
 * Unit tests for DirectAdmin wrapper class.
 *
 * @author Niels Keurentjes <niels.keurentjes@omines.com
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
        $context = DirectAdmin::connectAdmin(DIRECTADMIN_URL, ADMIN_USERNAME, ADMIN_PASSWORD);
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
        $context = DirectAdmin::connectAdmin(DIRECTADMIN_URL, ADMIN_USERNAME, ADMIN_PASSWORD, true);
        $this->assertEquals(ADMIN_USERNAME, $context->getUsername());
        $this->assertEquals(DirectAdmin::USERTYPE_ADMIN, $context->getType());
        return $context;
    }

    /**
     * @depends testAdminLogin
     */
    public function testCreateReseller(AdminContext $context)
    {
        // Create the reseller, and verify that afterwards there is 1 more reseller under the admin
        $before = count($context->getResellers());
        $reseller = $context->createReseller([
            'username' => RESELLER_USERNAME,
            'passwd' => RESELLER_PASSWORD,
            'email' => 'support@127.0.0.1',
            'domain' => 'phpunit.example.com',
        ]);
        $this->assertEquals($before + 1, count($context->getResellers()));

        // Check for list of domains
        $this->assertCount(1, $domains = $reseller->getDomains());
        $this->assertNotEmpty($default = $reseller->getDefaultDomain());
        /** @var Domain $firstDomain */
        $firstDomain = reset($domains);
        $this->assertEquals($firstDomain->getDomainName(), $default->getDomainName());

        // Fetch some global stuff
        $allUsers = $context->getAllUsers();
        $this->assertEquals(RESELLER_USERNAME, $context->getReseller(RESELLER_USERNAME)->getUsername());

        // Double check that all stats are at sane defaults
        $this->assertEquals(0, $firstDomain->getBandwidthUsed());
        $this->assertNull($firstDomain->getBandwidthLimit());
        $this->assertEquals(0, $firstDomain->getStorageUsed());

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

        // HACK: Get IPs quick for this test
        $ips = $resellerContext->invokeGet('SHOW_RESELLER_IPS');

        // Create a user in the reseller
        $before = count($resellerContext->getUsers());
        $user = $resellerContext->createUser([
            'username' => USER_USERNAME,
            'passwd' => USER_PASSWORD,
            'ip' => $ips[0],
            'email' => 'support@127.0.0.1',
            'domain' => 'phpunit.example.org',
        ]);
        $this->assertEquals($before + 1, count($resellerContext->getUsers()));

        // Impersonate to check things out
        $userContext = $user->impersonate();
        $this->assertEquals('phpunit.example.org', $userContext->getContextUser()->getDefaultDomain()->getDomainName());

        // Clean up
        $resellerContext->deleteUser(USER_USERNAME);
        $context->deleteReseller(RESELLER_USERNAME);

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
