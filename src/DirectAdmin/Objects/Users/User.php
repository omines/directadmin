<?php
/**
 * DirectAdmin
 * (c) Omines Internetbureau B.V.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Omines\DirectAdmin\Objects\Users;

use Omines\DirectAdmin\Context\ResellerContext;
use Omines\DirectAdmin\Context\UserContext;
use Omines\DirectAdmin\DirectAdmin;
use Omines\DirectAdmin\DirectAdminException;
use Omines\DirectAdmin\Objects\Domain;
use Omines\DirectAdmin\Objects\Object;

/**
 * User
 *
 * @author Niels Keurentjes <niels.keurentjes@omines.com
 */
class User extends Object
{
    /** @var array */
    protected $config;

    public function __construct($name, UserContext $context, $config = null)
    {
        parent::__construct($name, $context);
        $this->config = $config;
    }

    /**
     * @return string The username.
     */
    public function getUsername()
    {
        return $this->getName();
    }

    /**
     * @return Domain|null The default domain for the user, if any.
     */
    public function getDefaultDomain()
    {
        if(empty($name = $this->getConfig('domain')))
            return null;
        return new Domain($name, $this->getContext());
    }

    /**
     * @return Domain[]
     */
    public function getDomains()
    {
        // Thanks to DirectAdmin curious API some hackery required here
        $context = $this->getContext();
        if($context instanceof ResellerContext)
            $domains = $context->invokeGet('SHOW_USER_DOMAINS', ['user' => $this->getUsername()]);
        elseif($context->getUsername() === $this->getUsername())
            $domains = $context->invokeGet('SHOW_DOMAINS');
        else
            throw new DirectAdminException('At user level you can only request a list of your own domains');
        return Object::toRichObjectArray($domains, Domain::class, $context);
    }

    /**
     * @return string The user type, as one of the USERTYPE_ constants in the DirectAdmin class.
     */
    public function getType()
    {
        return $this->getConfig('usertype');
    }

    /**
     * Internal function to safe guard config changes and cache them.
     *
     * @param string $item Config item to retrieve.
     * @return mixed The value of the config item, or NULL.
     */
    private function getConfig($item)
    {
        if(!isset($this->config))
            $this->reload();
        return isset($this->config[$item]) ? $this->config[$item] : null;
    }

    /**
     * Reloads the current user config from the server, if it has been changed since last retrieved.
     */
    public function reload()
    {
        $this->config = $this->getContext()->invokeGet('SHOW_USER_CONFIG', ['user' => $this->getUsername()]);
    }

    /**
     * Constructs the correct object from the given user config.
     *
     * @param array $config The raw config from DirectAdmin.
     * @param UserContext $context The context within which the config was retrieved.
     * @return Admin|Reseller|User The correct object.
     * @throws DirectAdminException If the user type could not be determined.
     */
    public static function fromConfig($config, UserContext $context)
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
