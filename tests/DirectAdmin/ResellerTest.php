<?php

/*
 * DirectAdmin API Client
 * (c) Omines Internetbureau B.V. - https://omines.nl/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Omines\DirectAdmin\Context\AdminContext;
use Omines\DirectAdmin\Context\ResellerContext;
use Omines\DirectAdmin\DirectAdmin;
use Omines\DirectAdmin\Objects\Users\Reseller;

/**
 * ResellerTest.
 *
 * @author Niels Keurentjes <niels.keurentjes@omines.com>
 */
class ResellerTest extends \PHPUnit\Framework\TestCase
{
    /** @var AdminContext */
    private static $master;

    /** @var Reseller */
    private static $reseller;

    public static function setUpBeforeClass()
    {
        self::$master = DirectAdmin::connectAdmin(DIRECTADMIN_URL, MASTER_ADMIN_USERNAME, MASTER_ADMIN_PASSWORD);
        self::$reseller = self::$master->createReseller(RESELLER_USERNAME, RESELLER_PASSWORD, TEST_EMAIL, TEST_RESELLER_DOMAIN);
    }

    public static function tearDownAfterClass()
    {
        self::$master->deleteAccount(self::$reseller->getUsername());
    }

    public function testImpersonate()
    {
        $context = self::$reseller->impersonate();
        $this->assertEquals(self::$reseller->getUsername(), $context->getUsername());
        return $context;
    }

    /**
     * @depends testImpersonate
     */
    public function testGetUsers(ResellerContext $context)
    {
        $this->assertEmpty(self::$reseller->getUsers());
        $this->assertEmpty($context->getUsers());
        $this->assertNull(self::$reseller->getUser(USER_USERNAME));
        $this->assertNull($context->getUser(USER_USERNAME));
    }
}
