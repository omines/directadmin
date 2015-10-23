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
use Omines\DirectAdmin\Users\Admin;
use Omines\DirectAdmin\Users\Reseller;
use Omines\DirectAdmin\Users\User;

/**
 * DirectAdmin
 *
 * @author Niels Keurentjes <niels.keurentjes@omines.com
 */
abstract class DirectAdmin
{
    public static function connectAdmin($url, $username, $password)
    {
        return new Admin($url, $username, $password);
    }

    public static function connectReseller($url, $username, $password)
    {
        return new Reseller($url, $username, $password);
    }

    public static function connectUser($url, $username, $password)
    {
        return new User($url, $username, $password);
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

    public function invokeGet($command, $query = [])
    {
        return self::invoke('GET', $command, ['query' => $query]);
    }

    protected function invoke($method, $command, $options = [])
    {
        try
        {
            $response = $this->connection->request($method, '/' . $command, $options);
            $result = \GuzzleHttp\Psr7\parse_query($response->getBody());
            if(!empty($result['error']))
                throw new DirectAdminException($result['text']);
            return $result;
        }
        catch(TransferException $exception)
        {
            throw new DirectAdminException("API request $command using $method failed", 0, $exception);
        }
    }

    private $connection;
}
