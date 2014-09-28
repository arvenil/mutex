<?php
/**
 * This file is part of ninja-mutex.
 *
 * (C) Kamil Dziedzic <arvenil@klecza.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace NinjaMutex\Lock;

use NinjaMutex\UnrecoverableMutexException;

/**
 * Abstract for lock implementor using Memcache or Memcached
 *
 * @author Kamil Dziedzic <arvenil@klecza.pl>
 */
abstract class MemcacheLockAbstract extends LockAbstract
{
    /**
     * Memcache connection
     *
     * @var \Memcached|\Memcache
     */
    protected $memcache;
    protected $keys = array();

    /**
     * @param \Memcached|\Memcache $memcache
     */
    public function __construct($memcache)
    {
        parent::__construct();

        $this->memcache = $memcache;
    }

    /**
     * Try to release any obtained locks when object is destroyed
     *
     * This is a safe guard for cases when your php script dies unexpectedly.
     * It's not guaranteed it will work either.
     *
     * You should not depend on __destruct() to release your locks,
     * instead release them with `$released = $this->releaseLock()`A
     * and check `$released` if lock was properly released
     */
    public function __destruct()
    {
        foreach($this->keys as $name => $v) {
            $released = $this->releaseLock($name);
            if (!$released) {
                throw new UnrecoverableMutexException(sprintf(
                    'Cannot release lock in Memchache __destruct(): %s',
                    $name
                ));
            }
        }
    }

    public function __clone() {
        $this->keys = array();
    }

    /**
     * Acquire lock
     *
     * @param string $name name of lock
     * @param null|int $timeout 1. null if you want blocking lock
     *                          2. 0 if you want just lock and go
     *                          3. $timeout > 0 if you want to wait for lock some time (in milliseconds)
     * @return bool
     */
    public function acquireLock($name, $timeout = null)
    {
        $start = microtime(true);
        $end = $start + $timeout / 1000;
        $locked = false;
        while (!($locked = $this->getLock($name)) && $timeout > 0 && microtime(true) < $end) {
            usleep(static::USLEEP_TIME);
        }

        return $locked;
    }

    /**
     * @param string $name
     * @return bool
     */
    protected function getLock($name)
    {
        return empty($this->keys[$name]) && $this->memcache->add($name, serialize($this->getLockInformation())) && ($this->keys[$name] = true);
    }

    /**
     * Release lock
     *
     * @param string $name name of lock
     * @return bool
     */
    public function releaseLock($name)
    {
        if (isset($this->keys[$name]) && $this->memcache->delete($name)) {
            unset($this->keys[$name]);
            return true;
        }

        return false;
    }

    /**
     * Check if lock is locked
     *
     * @param string $name name of lock
     * @return bool
     */
    public function isLocked($name)
    {
        return false !== $this->memcache->get($name);
    }
}
