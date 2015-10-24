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
    /** @var float */
    private $bandwidthUsed;

    /** @var float */
    private $bandwidthLimit;

    /** @var float */
    private $storageUsed;

    public function __construct($name, UserContext $context, $config = null)
    {
        parent::__construct($name, $context);
        $config = explode(':', $config);
        $this->bandwidthUsed = floatval($config[0]);
        $this->bandwidthLimit = ($config[1] === 'unlimited' ? null : floatval($config[1]));
        $this->storageUsed = floatval($config[2]);
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
        return $this->bandwidthUsed;
    }

    /**
     * @return float|null
     */
    public function getBandwidthLimit()
    {
        return $this->bandwidthLimit;
    }

    /**
     * @return float
     */
    public function getStorageUsed()
    {
        return $this->storageUsed;
    }
}
