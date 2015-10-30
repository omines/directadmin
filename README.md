# DirectAdmin API client

[![Build Status](https://travis-ci.org/omines/directadmin.svg?branch=master)](https://travis-ci.org/omines/directadmin)
[![Coverage Status](https://coveralls.io/repos/omines/directadmin/badge.svg?branch=master&service=github)](https://coveralls.io/github/omines/directadmin?branch=master)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/47a71204-f274-4416-9db1-9773d65845ca/mini.png)](https://insight.sensiolabs.com/projects/47a71204-f274-4416-9db1-9773d65845ca)

This is a PHP client library to manage DirectAdmin control panel servers. We simply decided to develop this as we needed
automation of our own DirectAdmin servers, and the existing implementations were unsupported and incomplete.

As the DirectAdmin API is messy to say the least, and wildly inconsistent at best, expect the API to change
several times during initial development before we settle on a structure that both works and makes sense.

## Installation

The recommended way to install this library is from [Packagist](https://packagist.org/packages/omines/directadmin)
through [Composer](http://getcomposer.org).

```bash
composer require omines-directadmin:dev-master
```

The version specification is required until a stable version is released. Keep the note above in mind that the
public interface may change several times until we settle on something to go stable with.

If you're not familiar with `composer` follow the installation instructions for
[Linux/Unix/Mac](https://getcomposer.org/doc/00-intro.md#installation-linux-unix-osx) or
[Windows](https://getcomposer.org/doc/00-intro.md#installation-windows), and then read the
[basic usage introduction](https://getcomposer.org/doc/01-basic-usage.md).

## Dependencies

The library uses [Guzzle 6](https://github.com/guzzle/guzzle) as its HTTP communication layer. Minimum PHP
version supported is 5.5.0, as older versions are also End of Life.

## Usage

To set up the connection use one of the base functions:

```php
use Omines\DirectAdmin\DirectAdmin;

$adminContext = DirectAdmin::connectAdmin('http://hostname:2222', 'admin', 'pass');
$resellerContext = DirectAdmin::connectReseller('http://hostname:2222', 'reseller', 'pass');
$userContext = DirectAdmin::connectUser('http://hostname:2222', 'user', 'pass');
```

These functions return an `AdminContext`, `ResellerContext` and `UserContext` respectively exposing the
functionality available at the given level. All three extend eachother as DirectAdmin uses a strict is-a
model. To act on behalf of a user you can use impersonation calls:

```php
$resellerContext = $adminContext->impersonateReseller($resellerName);
$userContext = $resellerContext->impersonateUser($userName);
```
Both are essentially the same but mapped to the correct return type.

### Examples

The following examples all assume a context has been set up as described above.

#### Fetching all resellers and users

```php
foreach($adminContext->getResellers() as $resellerName => $reseller)
{
    // Loop over all users in the reseller account
    foreach($reseller->getUsers() as $userName => $user)
    {
        echo sprintf("User %s has default domain %s\n",
                     $user->getName(), $user->getDefaultDomain());
    }
}
```

#### Listing email forwarders

```php
var_dump(array_keys($userContext->getDomain('mydomain.tld')->getEmailForwarders()));
```

## Contributions

As the DirectAdmin API keeps expanding pull requests are welcomed, as are requests for specific functionality.
Pull requests should in general include proper unit tests for the implemented or corrected functions.

Unit tests are currently to be performed against a live server. To run them copy `phpunit.xml.dist` to
`phpunit.xml` and change the constants to reflect your own server. The URL and admin username and pass are
required to be valid, the other constants denote temporary objects that are created and removed during testing.

## Legal

This software was developed for internal use at [Omines Full Service Internetbureau](https://www.omines.nl/)
in Eindhoven, the Netherlands. It is shared with the general public under the permissive MIT license, without
any guarantee of fitness for any particular purpose. Refer to the included `LICENSE` file for more details.

The project is not in any way affiliated with JBMC Software or its employees.
