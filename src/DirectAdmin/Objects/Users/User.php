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
 * @author Niels Keurentjes <niels.keurentjes@omines.com>
 */
class User extends Object
{
    /** @var array */
    protected $config;

    /** @var Domain[] **/
    private $domains;

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
        return $this->getDomain($name);
    }

    /**
     * @param string $domainName
     * @return null|Domain
     */
    public function getDomain($domainName)
    {
        if(!isset($this->domains))
            $this->getDomains();
        return isset($this->domains[$domainName]) ? $this->domains[$domainName] : null;
    }

    /**
     * @return Domain[]
     */
    public function getDomains()
    {
        if(!isset($this->domains))
        {
            if(!$this->isSelfManaged())
                $this->domains = $this->impersonate()->getDomains();
            else
                $this->domains = Object::toRichObjectArray($this->getContext()->invokeGet('ADDITIONAL_DOMAINS'), Domain::class, $this->getContext());
        }
        return $this->domains;
    }

    /**
     * @return string The user type, as one of the ACCOUNT_TYPE_ constants in the DirectAdmin class.
     */
    public function getType()
    {
        return $this->getConfig('usertype');
    }

    /**
     * @return UserContext
     */
    public function impersonate()
    {
        /** @var ResellerContext $context */
        if(!($context = $this->getContext()) instanceof ResellerContext)
            throw new DirectAdminException('You need to be at least a reseller to impersonate');
        return $context->impersonateUser($this->getUsername());
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
            case DirectAdmin::ACCOUNT_TYPE_USER:
                return new User($name, $context, $config);
            case DirectAdmin::ACCOUNT_TYPE_RESELLER:
                return new Reseller($name, $context, $config);
            case DirectAdmin::ACCOUNT_TYPE_ADMIN:
                return new Admin($name, $context, $config);
            default:
                throw new DirectAdminException("Unknown user type '$config[usertype]'");
        }
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
     * @return bool Whether the account is managing itself.
     */
    protected function isSelfManaged()
    {
        return ($this->getUsername() === $this->getContext()->getUsername());
    }
}
