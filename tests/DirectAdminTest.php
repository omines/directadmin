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
use Omines\DirectAdmin\Objects\Users\User;

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
        $context = DirectAdmin::connectAdmin(DIRECTADMIN_URL, ADMIN_USERNAME, ADMIN_PASSWORD);
        $this->assertEquals(DirectAdmin::USERTYPE_ADMIN, $context->getUser()->getType());
        return $context;
    }

    /**
     * @depends testAdminLogin
     */
    public function testCreateReseller(AdminContext $context)
    {
        // Clean up test users first in case they got stuck after a failed unit test
        $this->cleanupTestAccounts($context);

        // Create the reseller, and verify that afterwards there is 1 more reseller under the admin
        $before = count($context->getResellers());
        $reseller = $context->createReseller([
            'username' => RESELLER_USERNAME,
            'passwd' => substr(sha1(__FILE__ . time()), 0, 10),
            'email' => 'support@127.0.0.1',
            'domain' => 'phpunit.example.com',
        ]);
        $this->assertEquals($before + 1, count($context->getResellers()));

        // Check for list of domains
        $this->assertCount(1, $domains = $reseller->getDomains());
        $this->assertEquals(reset($domains)->getDomainName(), $reseller->getDefaultDomain()->getDomainName());
    }

    /**
     * @depends testAdminLogin
     * @expectedException Omines\DirectAdmin\DirectAdminException
     */
    public function testInvalidUser(AdminContext $context)
    {
        // Ensure an invalid execution throws a proper exception
        $this->setExpectedException(DirectAdminException::class);
        $user = User::fromConfig(['username' => 'test', 'usertype' => 'test'], $context);
    }

    private function cleanupTestAccounts(AdminContext $context)
    {
        if($reseller = $context->getReseller(RESELLER_USERNAME))
        {
            if($user = $reseller->getUser(USER_USERNAME))
                $reseller->deleteUser(USER_USERNAME);
            $context->deleteReseller(RESELLER_USERNAME);
        }
    }
}
