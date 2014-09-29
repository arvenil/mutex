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

    /**
     * @param \Memcached|\Memcache $memcache
     */
    public function __construct($memcache)
    {
        parent::__construct();

        $this->memcache = $memcache;
    }

    /**
     * @param  string $name
     * @param  bool   $blocking
     * @return bool
     */
    protected function getLock($name, $blocking)
    {
        if (!$this->memcache->add($name, serialize($this->getLockInformation()))) {
            return false;
        }

        return true;
    }

    /**
     * Release lock
     *
     * @param  string $name name of lock
     * @return bool
     */
    public function releaseLock($name)
    {
        if (isset($this->locks[$name]) && $this->memcache->delete($name)) {
            unset($this->locks[$name]);

            return true;
        }

        return false;
    }

    /**
     * Check if lock is locked
     *
     * @param  string $name name of lock
     * @return bool
     */
    public function isLocked($name)
    {
        return false !== $this->memcache->get($name);
    }
}
