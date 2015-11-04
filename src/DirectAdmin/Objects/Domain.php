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
 * @author Niels Keurentjes <niels.keurentjes@omines.com>
 */
class Domain extends Object
{
    private $stats;

    /**
     * @param string $name
     * @param UserContext $context
     * @param array
     */
    public function __construct($name, UserContext $context, $stats)
    {
        parent::__construct($name, $context);
        $this->stats = explode(':', $stats);
    }

    public function getBandwidthUsed()
    {
        return floatval($this->stats[0]);
    }

    public function getDiskUsage()
    {
        return floatval($this->stats[2]);
    }

    public function getDomainName()
    {
        return $this->getName();
    }
}
