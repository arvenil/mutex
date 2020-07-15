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

use Predis;

/**
 * Lock implementor using Predis (client library for Redis)
 *
 * @author Kamil Dziedzic <arvenil@klecza.pl>
 */
class PredisRedisLock extends LockAbstract implements LockExpirationInterface
{
    /**
     * Predis connection
     *
     * @var Predis\Client
     */
    protected $client;

    /**
     * @var int Expiration time of the lock in seconds
     */
    protected $expiration = 0;

    /**
     * @param $client Predis\Client
     */
    public function __construct($client)
    {
        parent::__construct();

        $this->client = $client;
    }

    /**
     * @param int $expiration Expiration time of the lock in seconds
     */
    public function setExpiration($expiration)
    {
        $this->expiration = $expiration;
    }

    /**
     * @param  string $name
     * @param  bool   $blocking
     * @return bool
     */
    protected function getLock($name, $blocking)
    {
        /**
         * Perform the process recommended by Redis for acquiring a lock, from here: https://redis.io/commands/setnx
         * We are "C4" in this example...
         *
         * 1. C4 sends SETNX lock.foo in order to acquire the lock (sets the value if it does not already exist).
         * 2. The crashed client C3 still holds it, so Redis will reply with 0 to C4.
         * 3. C4 sends GET lock.foo to check if the lock expired.
         *    If it is not, it will sleep for some time and retry from the start.
         * 4. Instead, if the lock is expired because the Unix time at lock.foo is older than the current Unix time,
         *    C4 tries to perform:
         *    GETSET lock.foo <current Unix timestamp + lock timeout + 1>
         *    Because of the GETSET semantic, C4 can check if the old value stored at key is still an expired timestamp
         *    If it is, the lock was acquired.
         * 5. If another client, for instance C5, was faster than C4 and acquired the lock with the GETSET operation,
         *    the C4 GETSET operation will return a non expired timestamp.
         *    C4 will simply restart from the first step. Note that even if C4 wrote they key and set the expiry time
         *    a few seconds in the future this is not a problem. C5's timeout will just be a few seconds later.
         */

        $lockValue = $this->getLockInformation();
        if ($this->expiration) {
            // Add expiration timestamp to value stored in Redis.
            $lockValue['expires'] = time() + $this->expiration;
        }
        $lockValue = serialize($lockValue);

        if ($this->client->setnx($name, $lockValue)) {
            return true;
        }

        // Check if the existing lock has an expiry time. If it does and it has expired, delete the lock.
        if ($existingValue = $this->client->get($name)) {
            $existingValue = unserialize($existingValue);
            if (!empty($existingValue['expires']) && $existingValue['expires'] <= time()) {
                // The existing lock has expired. We can delete it and take over.
                $newExistingValue = unserialize($this->client->getset($name, $lockValue));

                // GETSET atomically sets key to value and returns the old value that was stored at key.
                // If the old value from getset does not still contain an expired timestamp
                // another probably acquired the lock in the meantime.
                if ($newExistingValue['expires'] > time()) {
                    return false;
                }

                // Got it!
                return true;
            }
        }

        return false;
    }

    /**
     * Release lock
     *
     * @param  string $name name of lock
     * @return bool
     */
    public function releaseLock($name)
    {
        if (isset($this->locks[$name]) && $this->client->del(array($name))) {
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
        return null !== $this->client->get($name);
    }

    /**
     * Forget a lock without releasing it
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
}
