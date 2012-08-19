<?php
/**
 * This file is part of Mutex.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package  Arvenil\Ninja\Mutex
 */
namespace Arvenil\Ninja\Mutex;

require_once 'LockAbstract.php';

/**
 * Lock implementor using MySql
 *
 * @author Kamil Dziedzic <arvenil@klecza.pl>
 */
class MySqlLock extends LockAbstract {
    /**
     * MySql connection
     * 
     * @var \PDO
     */
    protected $pdo;

    protected $lock = 'ble';

    public function __construct(\PDO $pdo) {
        parent::__construct();
        
        $this->pdo = $pdo;
    }

    /**
     * Acquire lock
     *
     * @param string $name name of lock
     * @param null|int $timeout 1. null if you want blocking lock
     *                          2. 0 if you want just lock and go
     *                          3. $timeout > 0 if you want to wait for lock some time (in miliseconds)
     * @return bool
     */
    public function acquireLock($name, $timeout = null) {
        $start = microtime(true);
        $end = $start + $timeout/1000;
        $locked = false;
        while (!($locked = $this->getLock($name)) && $timeout > 0 && microtime(true) < $end) {
            usleep(static::USLEEP_TIME);
        }

        return $locked;
    }

    protected function getLock($name) {
        return !$this->isLocked($name) && $this->pdo->query(
            sprintf(
                'SELECT GET_LOCK("%s", %d)',
                $name,
                0
            ),
            \PDO::FETCH_COLUMN, 0
        )->fetch();
    }

    /**
     * Release lock
     *
     * @param string $name name of lock
     * @return bool
     */
    public function releaseLock($name) {
        return (bool)$this->pdo->query(
            sprintf(
                'SELECT RELEASE_LOCK("%s")',
                $name
            ),
            \PDO::FETCH_COLUMN, 0
        )->fetch();
    }

    /**
     * Check if lock is locked
     *
     * @param string $name name of lock
     * @return bool
     */
    public function isLocked($name) {
        return !$this->pdo->query(
            sprintf(
                'SELECT IS_FREE_LOCK("%s")',
                $name
            ),
            \PDO::FETCH_COLUMN, 0
        )->fetch();
    }
}
