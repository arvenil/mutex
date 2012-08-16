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
 * Lock implementor using Memcache
 *
 * @author Kamil Dziedzic <arvenil@klecza.pl>
 */
class MemcacheLock extends LockAbstract {
    /**
     * Memcache connection
     * 
     * @var \Memcache
     */
    protected $memcache;

    /**
     * @param \Memcache $memcache
     */
    public function __construct(\Memcache $memcache) {
        parent::__construct();

        $this->memcache = $memcache;
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
    public function aquireLock($name, $timeout = null) {
        $start = microtime(true);
        $end = $start + $timeout/1000;
        $locked = false;
        while (!($locked = $this->memcache->add($name, 1)) && $timeout > 0 && microtime(true) < $end) {
            usleep(static::USLEEP_TIME);
        }

        return $locked;
    }

    /**
     * Release lock
     *
     * @param string $name name of lock
     * @return bool
     */
    public function releaseLock($name) {
        return $this->memcache->delete($name);
    }

    /**
     * Check if lock is locked
     *
     * @param string $name name of lock
     * @return bool
     */
    public function isLocked($name) {
        return false !== $this->memcache->get($name);
    }
}
