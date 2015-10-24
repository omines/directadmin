<?php
/**
 * DirectAdmin
 * (c) Omines Internetbureau B.V.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Omines\DirectAdmin\Objects;
use Omines\DirectAdmin\Context\BaseContext;

/**
 * Domain
 *
 * @author Niels Keurentjes <niels.keurentjes@omines.com
 */
class Domain extends Object
{
    public function __construct($name, BaseContext $context, $config = null)
    {
        parent::__construct($context);
        $this->username = $name;
        $config = explode(':', $config);
        var_dump($config);
    }
}
