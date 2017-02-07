<?php

/*
 * DirectAdmin API Client
 * (c) Omines Internetbureau B.V. - https://omines.nl/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * @author Niels Keurentjes <niels.keurentjes@omines.com>>
 */

define('PASSWORD_LENGTH', 16);

function generateTemporaryPassword()
{
    static $base = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $result = '';
    for ($i = 0; $i < PASSWORD_LENGTH; ++$i) {
        $result .= $base[mt_rand(0, strlen($base) - 1)];
    }
    for ($i = 0; $i < 100; ++$i) {
        $result = str_shuffle($result);
    }
    return $result;
}

$parameters = [
    'DIRECTADMIN_URL' => null,
    'MASTER_ADMIN_USERNAME' => null,
    'MASTER_ADMIN_PASSWORD' => null,
    'ADMIN_USERNAME' => 'testadmin',
    'ADMIN_PASSWORD' => generateTemporaryPassword(),
    'RESELLER_USERNAME' => 'testresell',
    'RESELLER_PASSWORD' => generateTemporaryPassword(),
    'USER_USERNAME' => 'testuser',
    'USER_PASSWORD' => generateTemporaryPassword(),
    'TEST_EMAIL' => 'example@127.0.0.1',
    'TEST_RESELLER_DOMAIN' => 'reseller.test.org',
    'TEST_USER_DOMAIN' => 'user.test.org',
];
foreach ($parameters as $parameter => &$value) {
    // Constants override environment
    if (defined($parameter)) {
        continue;
    }
    if (!isset($value) && empty($value = getenv($parameter))) {
        throw new RuntimeException("Required setting $parameter was neither set as a constant or an environment variable");
    }
    define($parameter, $value);
}

// Include composer autoload
require __DIR__ . str_replace('/', DIRECTORY_SEPARATOR, '/../vendor/autoload.php');

// Polyfill PHPUnit 6.0 both ways
if (!class_exists('\PHPUnit\Framework\TestCase', true)) {
    class_alias('\PHPUnit_Framework_TestCase', '\PHPUnit\Framework\TestCase');
} elseif (!class_exists('\PHPUnit_Framework_TestCase', true)) {
    class_alias('\PHPUnit\Framework\TestCase', '\PHPUnit_Framework_TestCase');
}
