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
 * Object
 *
 * @author Niels Keurentjes <niels.keurentjes@omines.com
 */
abstract class Object
{
    /** @var BaseContext */
    private $context;

    /**
     * @param BaseContext $context Context within which the object is valid.
     */
    protected function __construct(BaseContext $context)
    {
        $this->context = $context;
    }

    /**
     * @return BaseContext
     */
    public function getContext()
    {
        return $this->context;
    }
}
