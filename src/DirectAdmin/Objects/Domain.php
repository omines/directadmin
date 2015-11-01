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
    /**
     * @param string $name
     * @param UserContext $context
     */
    public function __construct($name, UserContext $context)
    {
        parent::__construct($name, $context);
    }

    public function getDomainName()
    {
        return $this->getName();
    }
}
