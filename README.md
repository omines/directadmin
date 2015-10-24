# DirectAdmin API client

PHP client library to manage DirectAdmin control panel servers. We simply decided to develop this as we needed
automation of our own DirectAdmin servers, and the existing implementations were unsupported and incomplete.

## Dependencies

The library uses [Guzzle 6](https://github.com/guzzle/guzzle) as its HTTP communication layer.

## Examples

Sample code for iterating over all resellers for an admin, and their respective users:

    use Omines\DirectAdmin\DirectAdmin;

    $context = DirectAdmin::connectAdmin('http://myserver.tld:2222', 'admin', 'password');
    foreach($context->getResellers() as $resellerName => $reseller)
        foreach($reseller->getUsers() as $userName => $user)
            echo sprintf("User %s has domain %s\n", $user->getName(), $user->getDefaultDomain());

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
