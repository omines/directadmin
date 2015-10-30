<?php
/**
 * DirectAdmin
 * (c) Omines Internetbureau B.V.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Omines\DirectAdmin;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\TransferException;
use Omines\DirectAdmin\Context\AdminContext;
use Omines\DirectAdmin\Context\ResellerContext;
use Omines\DirectAdmin\Context\UserContext;

/**
 * DirectAdmin API wrapper class.
 *
 * @author Niels Keurentjes <niels.keurentjes@omines.com
 */
class DirectAdmin
{
    const USERTYPE_ADMIN            = 'admin';
    const USERTYPE_RESELLER         = 'reseller';
    const USERTYPE_USER             = 'user';

    /** @var string Internal login name including impersonation. */
    private $loginName;

    /** @var string */
    private $username;

    /** @var string */
    private $password;

    /** @var string */
    private $baseUrl;

    /** @var Client */
    private $connection;

    public static function connectAdmin($url, $username, $password, $validate = false)
    {
        return new AdminContext(new self($url, $username, $password), $validate);
    }

    public static function connectReseller($url, $username, $password, $validate = false)
    {
        return new ResellerContext(new self($url, $username, $password), $validate);
    }

    public static function connectUser($url, $username, $password, $validate = false)
    {
        return new UserContext(new self($url, $username, $password), $validate);
    }

    protected function __construct($url, $username, $password)
    {
        $this->loginName = $username;
        $accounts = explode('|', $username);
        $this->username = end($accounts);
        $this->password = $password;
        $this->baseUrl = rtrim($url, '/') . '/';
        $this->connection = new Client([
            'base_uri' => $this->baseUrl,
            'auth' => [$username, $password],
            'http_errors' => true,
            'verify' => false
        ]);
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Invokes the DirectAdmin API with specific options.
     *
     * @param string $method HTTP method to use (ie. GET or POST)
     * @param string $command DirectAdmin API command to invoke.
     * @param array $options Guzzle options to use for the call.
     * @return array The unvalidated response.
     * @throws DirectAdminException If anything went wrong on the network level.
     */
    public function invoke($method, $command, $options = [])
    {
        try
        {
            $response = $this->connection->request($method, '/CMD_API_' . $command, $options);
            $contents = $response->getBody()->getContents();
            $elements = explode('/', $response->getHeader('Content-Type')[0]);
            switch(end($elements)) {
                case 'html':
                    // Malformed DirectAdmin HTML error
                    // TODO: Investigate why this is even happening, and if defendable reformat better
                    throw new DirectAdminException(trim(preg_replace('#(\s+)#', ' ', strip_tags($contents))));
            }
            $unescaped = preg_replace_callback('/&#([0-9]{2})/', function($val) {
                return chr($val[1]); }, $contents);
            $result = \GuzzleHttp\Psr7\parse_query($unescaped);
            if(!empty($result['error']))
                throw new DirectAdminException("$method to $command failed: $result[details] ($result[text])");
            elseif(count($result) == 1 && isset($result['list[]']))
                $result = $result['list[]'];
            if(!is_array($result))
                $result = [$result];
            return $result;
        }
        catch(TransferException $exception)
        {
            throw new DirectAdminException("API request $command using $method failed", 0, $exception);
        }
    }

    /**
     * Returns a clone of the connection logged in as a managed user or reseller.
     *
     * @param string $username
     * @return DirectAdmin
     */
    public function loginAs($username)
    {
        // DirectAdmin format is to just pipe the accounts together under the master password
        return new self($this->baseUrl, $this->loginName . "|{$username}", $this->password);
    }
}
