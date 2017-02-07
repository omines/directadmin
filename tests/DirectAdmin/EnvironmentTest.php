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
 * Tests for responses to invalid environment configuration.
 *
 * @author Niels Keurentjes <niels.keurentjes@omines.com>
 */
class EnvironmentTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @expectedException \Omines\DirectAdmin\DirectAdminException
     */
    public function testCorruptedUrl()
    {
        $admin = DirectAdmin::connectAdmin('noproto://www.google.com/', 'username', 'password');
        $admin->getContextUser()->getType();
    }

    /**
     * @expectedException \Omines\DirectAdmin\DirectAdminException
     */
    public function testInvalidUsername()
    {
        $admin = DirectAdmin::connectAdmin(DIRECTADMIN_URL, '_invalid', MASTER_ADMIN_PASSWORD);
        $admin->getContextUser()->getType();
    }

    /**
     * @expectedException \Omines\DirectAdmin\DirectAdminException
     */
    public function testInvalidPassword()
    {
        $admin = DirectAdmin::connectAdmin(DIRECTADMIN_URL, MASTER_ADMIN_USERNAME, MASTER_ADMIN_PASSWORD . '_invalid');
        $admin->getContextUser()->getType();
    }

    /**
     * @expectedException \Omines\DirectAdmin\DirectAdminException
     */
    public function testInvalidCall()
    {
        $admin = DirectAdmin::connectAdmin(DIRECTADMIN_URL, MASTER_ADMIN_USERNAME, MASTER_ADMIN_PASSWORD);
        $admin->invokeApiGet('INVALID_COMMAND');
    }

    /**
     * @expectedException \Omines\DirectAdmin\DirectAdminException
     */
    public function testInvalidUrl()
    {
        $admin = DirectAdmin::connectAdmin('http://www.google.com/', 'username', 'password');
        $admin->getContextUser()->getType();
    }
}
