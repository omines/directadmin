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
use Omines\DirectAdmin\DirectAdmin;
use Omines\DirectAdmin\DirectAdminException;

/**
 * User
 *
 * @author Niels Keurentjes <niels.keurentjes@omines.com
 */
class User extends Object
{
    protected $name;

    protected $config;

    public function __construct($name, BaseContext $context, $config = null)
    {
        parent::__construct($context);
        $this->name = $name;
        $this->config = $config;
    }

    public function getType()
    {
        return $this->config['usertype'];
    }

    public static function fromConfig($config, BaseContext $context)
    {
        $name = $config['username'];
        switch($config['usertype'])
        {
            case DirectAdmin::USERTYPE_USER:
                return new User($name, $context, $config);
            case DirectAdmin::USERTYPE_RESELLER:
                return new Reseller($name, $context, $config);
            case DirectAdmin::USERTYPE_ADMIN:
                return new Admin($name, $context, $config);
            default:
                throw new DirectAdminException("Unknown user type $config[usertype]");
        }
    }
}
