<?php
/**
 * DirectAdmin
 * (c) Omines Internetbureau B.V.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Omines\DirectAdmin\Objects\Database;

use Omines\DirectAdmin\Objects\Database;
use Omines\DirectAdmin\Objects\Object;

class AccessHost extends Object
{
    /** @var Database $database */
    protected $database;

    /**
     * @param string   $host
     * @param Database $database
     */
    public function __construct($host, Database $database)
    {
        parent::__construct($host, $database->getContext());
        $this->database = $database;
    }

    /**
     * @param Database $database
     * @param string   $host
     * @return AccessHost
     */
    public static function create(Database $database, $host)
    {
        $database->getContext()->invokePost('DATABASES', [
            'action' => 'accesshosts',
            'create' => 'yes',
            'db' => $database->getDatabaseName(),
            'host' => $host,
        ]);
        return new self($host, $database);
    }

    /**
     * Deletes the access host
     */
    public function delete()
    {
        $this->getContext()->invokePost('DATABASES', [
            'action' => 'accesshosts',
            'delete' => 'yes',
            'db' => $this->database->getDatabaseName(),
            'select0' => $this->getName(),
        ]);
    }

    /**
     * @return string
     */
    public function getHost()
    {
        return $this->getName();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getHost();
    }
}
