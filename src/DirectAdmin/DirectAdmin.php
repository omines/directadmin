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
use Omines\DirectAdmin\UserTypes\Admin;
use Omines\DirectAdmin\UserTypes\Reseller;
use Omines\DirectAdmin\UserTypes\User;

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

    public static function connectAdmin($url, $username, $password, $validate = false)
    {
        return new Admin($url, $username, $password, $validate);
    }

    public static function connectReseller($url, $username, $password, $validate = false)
    {
        return new Reseller($url, $username, $password, $validate);
    }

    public static function connectUser($url, $username, $password)
    {
        return new User($url, $username, $password);
    }

    public static function connectAuto($url, $username, $password)
    {
        $server = new self($url, $username, $password);

    }

    protected function __construct($url, $username, $password)
    {
        $this->connection = new Client([
            'base_uri' => rtrim($url, '/') . '/',
            'auth' => [$username, $password],
            'http_errors' => true,
            'verify' => false
        ]);
    }

    /**
     * Invokes the DirectAdmin API via HTTP GET.
     *
     * @param string $command DirectAdmin API command to invoke.
     * @param array $query Optional query parameters.
     * @return array The parsed and validated response.
     */
    public function invokeGet($command, $query = [])
    {
        return self::invoke('GET', $command, ['query' => $query]);
    }

    /**
     * Invokes the DirectAdmin API with specific options.
     *
     * @param string $method HTTP method to use (ie. GET or POST)
     * @param string $command DirectAdmin API command to invoke.
     * @param array $options Guzzle options to use for the call.
     * @return mixed The unvalidated response.
     * @throws DirectAdminException If anything went wrong on the network level.
     */
    protected function invoke($method, $command, $options = [])
    {
        try
        {
            $response = $this->connection->request($method, '/CMD_API_' . $command, $options);
            $contents = $response->getBody()->getContents();
            $unescaped = preg_replace_callback('/&#([0-9]{2})/', function($val) {
                return chr($val[1]); }, $contents);
            parse_str($unescaped, $result);
            if(!empty($result['error']))
                throw new DirectAdminException("$method to $command failed: $result[details] ($result[text])");
            if(count($result) == 1 && isset($result['list']))
                $result = $result['list'];
            return $result;
        }
        catch(TransferException $exception)
        {
            throw new DirectAdminException("API request $command using $method failed", 0, $exception);
        }
    }

    /**
     * Throws exception if any of the required options are not set.
     *
     * @param array $options Associative array of options.
     * @param array $required Flat array of required options.
     */
    protected static function checkMandatoryOptions(array $options, array $required)
    {
        if(!empty($diff = array_diff($required, array_keys($options))))
            throw new DirectAdminException('Missing required options: ' . implode(', ', $diff));
    }

    /** @var Client */
    private $connection;
}
