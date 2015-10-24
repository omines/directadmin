<?php
/**
 * DirectAdmin
 * (c) Omines Internetbureau B.V.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Omines\DirectAdmin\Objects;

/**
 * Admin
 *
 * @author Niels Keurentjes <niels.keurentjes@omines.com
 */
class Admin extends Reseller
{
    private $resellers;

    public function getReseller($username)
    {
        $resellers = $this->getResellers();
        return isset($resellers[$username]) ? $resellers[$username] : null;
    }

    public function getResellers()
    {
        if(!isset($this->resellers))
        {
            $resellers = $this->getContext()->invokeGet('SHOW_RESELLERS');
            $this->resellers = array_combine($resellers, array_map(function($reseller) { return new Reseller($reseller, $this->getContext()); }, $resellers));
        }
        return $this->resellers;
    }
}
