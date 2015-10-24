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
    /**
     * @param string $username
     * @return null|Reseller
     */
    public function getReseller($username)
    {
        $resellers = $this->getResellers();
        return isset($resellers[$username]) ? $resellers[$username] : null;
    }

    /**
     * @return Reseller[]
     */
    public function getResellers()
    {
        $resellers = $this->getContext()->invokeGet('SHOW_RESELLERS');
        return array_combine($resellers, array_map(function($reseller) { return new Reseller($reseller, $this->getContext()); }, $resellers));
    }
}
