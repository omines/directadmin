<?php
/**
 * DirectAdmin
 * (c) Omines Internetbureau B.V.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Omines\DirectAdmin\Objects\Email;

use Omines\DirectAdmin\Context\UserContext;
use Omines\DirectAdmin\Objects\Domain;
use Omines\DirectAdmin\Objects\DomainObject;

/**
 * Encapsulates an email forwarder.
 *
 * @author Niels Keurentjes <niels.keurentjes@omines.com>
 */
class Forwarder extends DomainObject
{
    /** @var string[] */
    private $recipients;

    /**
     * Construct the object.
     *
     * @param string $name The domain name.
     * @param UserContext $context The owning user context.
     * @param Domain $domain The containing domain.
     * @param array|string $recipients Array or string containing the recipients.
     */
    public function __construct($name, UserContext $context, Domain $domain, $recipients)
    {
        parent::__construct($name, $context, $domain);
        $this->recipients = is_string($recipients) ? array_map('trim', explode(',', $recipients)) : $recipients;
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

    /**
     * @return string[]
     */
    public function getRecipients()
    {
        return $this->recipients;
    }

    /**
     * Returns the domain-agnostic part before the @ in the forwarder.
     *
     * @return string
     */
    public function getAliases()
    {
        $prefix = $this->getPrefix();
        foreach($this->getDomain()->getAliasesAndPointers() as $domain)
            yield "$prefix@$domain";
    }
}
