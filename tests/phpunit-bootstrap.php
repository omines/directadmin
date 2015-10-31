<?php
/**
 * DirectAdmin
 * (c) Omines Internetbureau B.V.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Niels Keurentjes <niels.keurentjes@omines.com>
 */

define('PASSWORD_LENGTH', 16);

function generateTemporaryPassword()
{
    static $base = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $result = '';
    for($i = 0; $i != PASSWORD_LENGTH; $i++)
        $result .= $base[mt_rand(0, strlen($base)-1)];
    for($i = 0; $i != 100; $i++)
        $result = str_shuffle($result);
    return $result;
}

$parameters = [
    'DIRECTADMIN_URL'       => null,
    'MASTER_ADMIN_USERNAME' => null,
    'MASTER_ADMIN_PASSWORD' => null,
    'ADMIN_USERNAME'        => 'testadmin',
    'ADMIN_PASSWORD'        => generateTemporaryPassword(),
    'RESELLER_USERNAME'     => 'testresell',
    'RESELLER_PASSWORD'     => generateTemporaryPassword(),
    'USER_USERNAME'         => 'testuser',
    'USER_PASSWORD'         => generateTemporaryPassword(),
];
foreach($parameters as $parameter => &$value)
{
    if(!isset($value) && empty($value = getenv($parameter)))
        throw new RuntimeException("Required setting $parameter was neither set as a constant or an environment variable");
    define($parameter, $value);
}

// Include composer autoload
require __DIR__ . str_replace('/', DIRECTORY_SEPARATOR, '/../vendor/autoload.php');
