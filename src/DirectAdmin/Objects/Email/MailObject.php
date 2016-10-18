<?php

/*
 * DirectAdmin API Client
 * (c) Omines Internetbureau B.V. - https://omines.nl/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Omines\DirectAdmin\Objects\Email;

use Omines\DirectAdmin\Objects\DomainObject;

/**
 * Base class for objects exposing a mail address.
 *
 * @author Niels Keurentjes <niels.keurentjes@omines.com>
 */
abstract class MailObject extends DomainObject
{
    /**
     * Delete the object.
     *
     * @param string $command Command to execute
     * @param string $paramName Parameter name for the delete command
     */
    protected function invokeDelete($command, $paramName)
    {
        $this->invokePost($command, 'delete', [$paramName => $this->getPrefix()]);
    }

    /**
     * Returns the full email address for this forwarder.
     *
     * @return string
     */
    public function getEmailAddress()
    {
        return $this->getPrefix() . '@' . $this->getDomainName();
    }

    /**
     * Returns the domain-agnostic part before the @ in the forwarder.
     *
     * @return string
     */
    public function getPrefix()
    {
        return $this->getName();
    }
}
