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

use NinjaMutex\Lock\LockAbstract;
use Predis;

/**
 * Lock implementor using Predis (client library for Redis)
 *
 * @author Kamil Dziedzic <arvenil@klecza.pl>
 */
class PredisRedisLock extends LockAbstract
{
    /**
     * Predis connection
     *
     * @var
     */
    protected $client;
    protected $keys = array();

    /**
     * @param $client Predis\Client
     */
    public function __construct(Predis\Client $client)
    {
        parent::__construct();

        $this->client = $client;
    }

    public function __destruct()
    {
        foreach($this->keys as $name => $v) {
            $this->releaseLock($name);
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
        return empty($this->keys[$name]) && $this->client->setnx($name, serialize($this->getLockInformation())) && ($this->keys[$name] = true);
    }

    /**
     * Release lock
     *
     * @param string $name name of lock
     * @return bool
     */
    public function releaseLock($name)
    {
        if (isset($this->keys[$name]) && $this->client->del($name)) {
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
        return null !== $this->client->get($name);
    }
}
