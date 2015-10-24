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
    /** @var string */
    protected $name;

    protected $config;

    public function __construct($name, BaseContext $context, $config = null)
    {
        parent::__construct($context);
        $this->name = $name;
        $this->config = $config;
    }

    /**
     * @return string The username.
     */
    public function getName()
    {
        return $this->name;
    }

    public function getDefaultDomain()
    {
        return $this->getConfig('domain');
    }

    public function getType()
    {
        return $this->getConfig('usertype');
    }

    private function getConfig($item)
    {
        if(!isset($this->config))
            $this->config = $this->getContext()->invokeGet('SHOW_USER_CONFIG', ['user' => $this->name]);
        return isset($this->config[$item]) ? $this->config[$item] : null;
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
