<?php
/**
 * DirectAdmin
 * (c) Omines Internetbureau B.V.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Omines\DirectAdmin\Context\AdminContext;
use Omines\DirectAdmin\Context\UserContext;
use Omines\DirectAdmin\DirectAdmin;
use Omines\DirectAdmin\Objects\Users\User;

/**
 * UserTest
 *
 * @author Niels Keurentjes <niels.keurentjes@omines.com>
 */
class UserTest extends \PHPUnit_Framework_TestCase
{
    /** @var AdminContext */
    private static $master;

    /** @var User */
    private static $user;

    public static function setUpBeforeClass()
    {
        self::$master = DirectAdmin::connectAdmin(DIRECTADMIN_URL, MASTER_ADMIN_USERNAME, MASTER_ADMIN_PASSWORD);
        $ips = self::$master->getIPs();
        self::$user = self::$master->createUser(USER_USERNAME, USER_PASSWORD, TEST_EMAIL, TEST_USER_DOMAIN, $ips[0]);
    }

    public static function tearDownAfterClass()
    {
        self::$master->deleteAccount(self::$user->getUsername());
    }

    public function testImpersonate()
    {
        $context = self::$user->impersonate();
        $this->assertEquals(self::$user->getUsername(), $context->getUsername());
        return $context;
    }

    /**
     * @depends testImpersonate
     * @expectedException \Omines\DirectAdmin\DirectAdminException
     * @expectedExceptionMessage Unknown user type 'invalid'
     */
    public function testInvalidUserType(UserContext $context)
    {
        User::fromConfig([
            'username' => 'invalid',
            'usertype' => 'invalid'
        ], $context);
    }

    /**
     * @depends testImpersonate
     */
    public function testDomainStats(UserContext $context)
    {
        $domainAsAdmin = self::$user->getDomain(TEST_USER_DOMAIN);
        $domainAsUser = $context->getDomain(TEST_USER_DOMAIN);
        $this->assertEquals($domainAsAdmin->getBandwidthUsed(), $domainAsUser->getBandwidthUsed());
        $this->assertEquals($domainAsAdmin->getDiskUsage(), $domainAsUser->getDiskUsage());
        $this->assertEquals($context, $domainAsUser->getContext());

        // Should be no further objects or settings yet
        $this->assertEmpty($domainAsUser->getAliases());
        $this->assertNull($domainAsUser->getBandwidthLimit());
        $this->assertEmpty($domainAsUser->getPointers());
        $this->assertEmpty($domainAsUser->getEmailForwarders());
        $this->assertEmpty($domainAsUser->getMailboxes());
    }
}
