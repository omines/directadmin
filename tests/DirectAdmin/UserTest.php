<?php

/*
 * DirectAdmin API Client
 * (c) Omines Internetbureau B.V. - https://omines.nl/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Omines\DirectAdmin\Context\AdminContext;
use Omines\DirectAdmin\Context\UserContext;
use Omines\DirectAdmin\DirectAdmin;
use Omines\DirectAdmin\Objects\Domain;
use Omines\DirectAdmin\Objects\Users\User;

/**
 * UserTest.
 *
 * @author Niels Keurentjes <niels.keurentjes@omines.com>
 */
class UserTest extends \PHPUnit\Framework\TestCase
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
            'usertype' => 'invalid',
        ], $context);
    }

    public function testDefaultDomain()
    {
        $domain = self::$user->getDefaultDomain();
        $this->assertEquals(TEST_USER_DOMAIN, $domain->getDomainName());
        return $domain;
    }

    /**
     * @depends testImpersonate
     */
    public function testDomainStats(UserContext $context)
    {
        $domainAsAdmin = self::$user->getDomain(TEST_USER_DOMAIN);
        $domainAsUser = $context->getDomain(TEST_USER_DOMAIN);
        $this->assertEquals(strval($domainAsAdmin), strval($domainAsUser));
        $this->assertEquals($domainAsAdmin->getBandwidthUsed(), $domainAsUser->getBandwidthUsed());
        $this->assertEquals($domainAsAdmin->getDiskUsage(), $domainAsUser->getDiskUsage());
        $this->assertEquals($context, $domainAsUser->getContext());
        $this->assertEquals(self::$user->getUsername(), $domainAsUser->getOwner()->getUsername());

        // Should be no further objects or settings yet
        $this->assertEmpty($domainAsUser->getAliases());
        $this->assertNull($domainAsUser->getBandwidthLimit());
        $this->assertEmpty($domainAsUser->getPointers());
        $this->assertEmpty($domainAsUser->getForwarders());
        $this->assertEmpty($domainAsUser->getMailboxes());
    }

    /**
     * @expectedException \Omines\DirectAdmin\DirectAdminException
     * @expectedExceptionMessage Could not determine relationship between context user and domain
     */
    public function testDomainCorruption()
    {
        new Domain('example.org', self::$master, ['domain' => 'example.org', 'username' => 'invalid']);
    }

    public function testUserStats()
    {
        $user = self::$user;

        // Assert the user is not suspended while of the correct type
        $this->assertFalse($user->isSuspended());
        $this->assertEquals(DirectAdmin::ACCOUNT_TYPE_USER, $user->getType());

        // It should not have any usage yet except a single domain
        $this->assertEquals(0, $user->getBandwidthUsage());
        $this->assertNull($user->getBandwidthLimit());
        $this->assertEquals(0, $user->getDatabaseUsage());
        $this->assertNull($user->getDatabaseLimit());
        $this->assertEquals(1, $user->getDomainUsage());
        $this->assertNull($user->getDomainLimit());
        $this->assertEquals(0, $user->getDiskUsage());
        $this->assertNull($user->getDiskLimit());
    }

    public function testDomains()
    {
        $user = self::$user;

        // Create some domains with various settings
        $domain1 = $user->createDomain('example1.org');
        $domain2 = $user->createDomain('example2.org', 50);
        $domain3 = $user->createDomain('example3.org', 200, 50, false, null, true);
        $this->assertCount(4, $domains = $user->getDomains());

        // Delete all added domains
        $domain1->delete();
        $domain2->delete();
        $domain3->delete();
        $user->clearCache(); // This shouldn't be necessary...?
        $this->assertCount(1, $user->getDomains());
    }

    public function testDatabases()
    {
        $user = self::$user;

        // Create 3 databases of which 1 with double user
        $db1 = $user->createDatabase('test1', 'test2', 'test3');
        $user->createDatabase('test2', 'test2');
        $user->createDatabase('test3', 'test3', 'test4');
        $this->assertCount(3, $databases = $user->getDatabases());
        $this->assertEquals($db1->getOwner()->getUsername(), $user->getUsername());

        // Add access host
        $db1->createAccessHost('192.168.1.1');
        $this->assertCount(2, $hosts = $db1->getAccessHosts());
        $this->assertEquals('192.168.1.1', $hosts[0]->getHost());
        $hosts[0]->delete();
        $this->assertCount(1, $db1->getAccessHosts());

        // Delete all of them
        $db1->delete();
        $user->clearCache(); // Buggy...
        $this->assertCount(2, $databases = $user->getDatabases());
        $databases['test2']->delete();
        $databases['test3']->delete();
        $user->clearCache(); // Buggy...
        $this->assertEmpty($user->getDatabases());
    }

    public function testUserQuota()
    {
        $user = self::$user;

        // Set some quota
        $user->setBandwidthLimit(25000);
        $user->setDiskLimit(500);
        $user->setDomainLimit(5);

        // Verify the settings were applied correctly
        $this->assertEquals(25000, $user->getBandwidthLimit());
        $this->assertEquals(500, $user->getDiskLimit());
        $this->assertEquals(5, $user->getDomainLimit());

        // Unset some quota (implying unlimited) and validate them
        $user->setBandwidthLimit(null);
        $user->setDiskLimit(null);
        $this->assertNull($user->getBandwidthLimit());
        $this->assertNull($user->getDiskLimit());
    }

    /**
     * @depends testDefaultDomain
     */
    public function testCatchall(Domain $domain)
    {
        self::$user->setAllowCatchall(true);
        $domain->setCatchall('aap@noot.mies');
        $this->assertEquals('aap@noot.mies', $domain->getCatchall());
        $domain->setCatchall(Domain::CATCHALL_BLACKHOLE);
        $this->assertEquals(Domain::CATCHALL_BLACKHOLE, $domain->getCatchall());
        $domain->setCatchall(Domain::CATCHALL_FAIL);
        $this->assertEquals(Domain::CATCHALL_FAIL, $domain->getCatchall());
    }

    /**
     * @depends testDefaultDomain
     */
    public function testForwarders(Domain $domain)
    {
        // Create 2 forwarders after asserting they are the first
        $this->assertEmpty($domain->getForwarders());
        $domain->createForwarder('single', 'single@example.org');
        $domain->createForwarder('multiple', ['recipient@example.org', 'recipient@gmail.com']);
        $this->assertCount(2, $forwarders = $domain->getForwarders());

        // Manage single forwarder
        $forwarder = $forwarders['single'];
        $this->assertEquals('single', $forwarder->getPrefix());
        $this->assertContains('single@example.org', $forwarder->getRecipients());
        $this->assertContains('single@' . TEST_USER_DOMAIN, $forwarder->getAliases());

        // Delete a forwarder and ensure domain stats are updated
        $forwarders['single']->delete();
        $this->assertCount(1, $domain->getForwarders());
    }

    /**
     * @depends testDefaultDomain
     */
    public function testMailboxes(Domain $domain)
    {
        // Create 2 forwarders after asserting they are the first
        $this->assertEmpty($domain->getMailboxes());
        $mail1 = $domain->createMailbox('mail1', generateTemporaryPassword());
        $mail2 = $domain->createMailbox('mail2', generateTemporaryPassword(), 500, 50);
        $this->assertCount(2, $boxes = $domain->getMailboxes());

        // Check mailbox statistics
        $this->assertEquals('mail1@' . TEST_USER_DOMAIN, $boxes['mail1']->getEmailAddress());
        $this->assertNull( $mail1->getDiskLimit() );
        $this->assertEquals(500, $mail2->getDiskLimit());
        $this->assertEquals(0, $mail1->getDiskUsage(), 'Disk usage should be near empty', 0.1);
        $this->assertEquals(0, $mail2->getMailsSent());
        $this->assertEquals(50, $mail2->getMailLimit());
        $this->assertFalse( $mail2->getMailSuspended() );

        // Changing password should not throw any errors
        $mail1->setPassword(generateTemporaryPassword());

        // Delete the mailbox and ensure domain stats are updated
        $boxes['mail2']->delete();
        $this->assertCount(1, $domain->getMailboxes());
        $mail1->delete();
        $this->assertEmpty($domain->getMailboxes());
    }

    /**
     * @depends testDefaultDomain
     */
    public function testPointers(Domain $domain)
    {
        $domain->createPointer('invalid.pointer.org');
        $domain->createPointer('invalid.alias.org', true);
        $this->assertCount(1, $aliases = $domain->getAliases());
        $this->assertCount(1, $pointers = $domain->getPointers());
        $this->assertEquals('invalid.pointer.org', $pointers[0]);
        $this->assertEquals('invalid.alias.org', $aliases[0]);
    }

    /**
     * @depends testDefaultDomain
     */
    public function testSubdomains(Domain $domain)
    {
        // Create 2 subdomains after asserting they are the first
        $this->assertEmpty($domain->getSubdomains());
        $sub1 = $domain->createSubdomain('sub1');
        $sub2 = $domain->createSubdomain('sub2');
        $this->assertCount(2, $subdomains = $domain->getSubdomains());

        // Check properties
        $this->assertEquals($sub1->getPrefix(), $subdomains['sub1']->getPrefix());
        $this->assertEquals($sub2->getDomainName(), strval($subdomains['sub2']));
        $this->assertEquals('sub1.' . TEST_USER_DOMAIN, $sub1->getDomainName());
        $this->assertEquals(TEST_USER_DOMAIN, $sub1->getBaseDomainName());

        // Check deletion
        $sub1->delete();
        $this->assertCount(1, $domain->getSubdomains());
        $subdomains['sub2']->delete(false);
        $this->assertEmpty($domain->getSubdomains());
    }
}
