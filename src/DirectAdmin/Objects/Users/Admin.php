<?php
/**
 * DirectAdmin
 * (c) Omines Internetbureau B.V.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Omines\DirectAdmin\Objects\Users;

use Omines\DirectAdmin\Context\AdminContext;

/**
 * Admin
 *
 * @author Niels Keurentjes <niels.keurentjes@omines.com
 */
class Admin extends Reseller
{
    /**
     * @inheritdoc
     */
    public function __construct($name, AdminContext $context, $config = null)
    {
        parent::__construct($name, $context, $config);
    }
}
