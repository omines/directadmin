<?php
/**
 * DirectAdmin
 * (c) Omines Internetbureau B.V.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Omines\DirectAdmin\Objects;

use Omines\DirectAdmin\Context\UserContext;

/**
 * Domain
 *
 * @author Niels Keurentjes <niels.keurentjes@omines.com
 */
class Domain extends Object
{
    /** @var array */
    private $config;

    /**
     * @param string $name
     * @param UserContext $context
     * @param array|string|null $config
     */
    public function __construct($name, UserContext $context, $config = null)
    {
        parent::__construct($name, $context);
        if(is_string($config))
            $this->config = explode(':', $config);
        else
            $this->config = $config;
    }

    public function getDomainName()
    {
        return $this->getName();
    }

    /**
     * @return float
     */
    public function getBandwidthUsed()
    {
        return floatval($this->config[0]);
    }

    /**
     * @return float|null
     */
    public function getBandwidthLimit()
    {
        return ($this->config[1] === 'unlimited' ? null : floatval($this->config[1]));
    }

    public function getEmailForwarders()
    {
        return $this->getContext()->invokeGet('EMAIL_FORWARDERS', ['domain' => $this->getDomainName()]);
    }

    public function getMailboxes()
    {
        return $this->getContext()->invokeGet('POP', ['domain' => $this->getDomainName(), 'action' => 'list']);
    }

    /**
     * @return float
     */
    public function getStorageUsed()
    {
        return floatval($this->config[2]);
    }
}
