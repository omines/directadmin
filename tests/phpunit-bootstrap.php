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

$required = [
    'DIRECTADMIN_URL',
    'ADMIN_USERNAME',
    'ADMIN_PASSWORD',
    'RESELLER_USERNAME',
    'RESELLER_PASSWORD',
    'USER_USERNAME',
    'USER_PASSWORD',
];
foreach($required as $entry)
{
    if(!defined($entry))
    {
        if(empty($value = getenv($entry)))
            throw new RuntimeException("Required setting $entry was neither set as a constant or an environment variable");
        define($entry, $value);
    }
}

// Include composer autoload
require __DIR__ . '/../vendor/autoload.php';
