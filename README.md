# DirectAdmin API interface

PHP interface to manage DirectAdmin control panel servers.

## Usage

    use Omines\DirectAdmin\DirectAdmin;

    $context = DirectAdmin::connectAdmin('http://myserver.tld:2222', 'admin', 'password');
    foreach($context->getResellers() as $reseller)
      foreach($context->getReseller($reseller)->getUsers() as $user)
        echo sprintf("User %s has domain %s\n", $user->getUsername(), $user->getDefaultDomain());

## Contributions

As the DirectAdmin API keeps expanding pull requests are welcomed, as are requests for specific functionality.
Pull requests should in general include proper unit tests for the implemented or corrected functions.

## Legal

This software was developed for internal use at [Omines Full Service Internetbureau](https://www.omines.nl/)
in Eindhoven, the Netherlands. It is shared with the general public under the permissive MIT license, without
any guarantee of fitness for any particular purpose. Refer to the included `LICENSE` file for more details.

The project is not in any way affiliated with JBMC Software or its employees.
