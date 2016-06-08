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

use Memcached;

/**
 * Lock implementor using Memcached
 *
 * @author Kamil Dziedzic <arvenil@klecza.pl>
 */
class MemcachedLock extends LockAbstract implements LockExpirationInterface
{
    /**
     * Maximum expiration time in seconds (30 days)
     * http://php.net/manual/en/memcached.add.php
     */
    const MAX_EXPIRATION = 2592000;

    /**
     * Memcache connection
     *
     * @var Memcached
     */
    protected $memcached;

    /**
     * @var int Expiration time of the lock in seconds
     */
    protected $expiration = 0;

    /**
     * @param Memcached $memcached
     */
    public function __construct($memcached)
    {
        parent::__construct();

        $this->memcached = $memcached;
    }

    /**
     * @param int $expiration Expiration time of the lock in seconds. If it's equal to zero (default), the lock will never expire.
     *                        Max 2592000s (30 days), if greater it will be capped to 2592000 without throwing an error.
     *                        WARNING: Using value higher than 0 may lead to race conditions. If you set too low expiration time
     *                        e.g. 30s and critical section will run for 31s another process will gain lock at the same time,
     *                        leading to unpredicted behaviour. Use with caution.
     */
    public function setExpiration($expiration)
    {
        if ($expiration > static::MAX_EXPIRATION) {
            $expiration = static::MAX_EXPIRATION;
        }
        $this->expiration = $expiration;
    }

    /**
     * Clear lock without releasing it
     * Do not use this method unless you know what you do
     *
     * @param  string $name name of lock
     * @return bool
     */
    public function clearLock($name)
    {
        if (!isset($this->locks[$name])) {
            return false;
        }

        unset($this->locks[$name]);
        return true;
    }

    /**
     * @param  string $name name of lock
     * @param  bool   $blocking
     * @return bool
     */
    protected function getLock($name, $blocking)
    {
        if (!$this->memcached->add($name, serialize($this->getLockInformation()), $this->expiration)) {
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
        if (isset($this->locks[$name]) && $this->memcached->delete($name)) {
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
    public function isLocked($name) {
        $value = $this->memcached->get($name);
        if ($value) {
            $values = unserialize($value);
            // Additional verification, just in case the lock was acquired by a process that doesn't exist anymore
            $informations = $this->generateLockInformation();
            $output = null;
            // @todo the following command has only be tested on Debian8 & MacosX
            exec("ps -p {$values[0]}", $output);
            // If the original server is the same that the one we're on, but the process has gone, let's remove the LOCK from Memcache
            if($informations[2] == $values[2] && (count($output) == 1)) {
                $this->memcached->delete($name);
                return false;
            } else {
                return true;
            }
        } else {
            // Value doesn't exist, so there is no lock
            return false;
        }
    }
}

