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
 * Abstract lock implementor
 *
 * @author Kamil Dziedzic <arvenil@klecza.pl>
 */
abstract class LockAbstract implements LockInterface
{
    const USLEEP_TIME = 100;

    /**
     * Provides information which allows to track down process which acquired lock
     *
     * @var LockInformationProviderInterface
     */
    protected $lockInformationProvider;

    /**
     * @var array
     */
    protected $locks = array();

    public function __construct(LockInformationProviderInterface $informationProvider = null)
    {
        $this->lockInformationProvider = $informationProvider ? : new BasicLockInformationProvider();
    }

    public function __clone()
    {
        $this->locks = array();
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
        foreach ($this->locks as $name => $v) {
            $released = $this->releaseLock($name);
            if (!$released) {
                throw new UnrecoverableMutexException(sprintf(
                    'Cannot release lock in __destruct(): %s',
                    $name
                ));
            }
        }
    }

    /**
     * Acquire lock
     *
     * @param  string   $name    name of lock
     * @param  null|int $timeout 1. null if you want blocking lock
     *                           2. 0 if you want just lock and go
     *                           3. $timeout > 0 if you want to wait for lock some time (in milliseconds)
     * @return bool
     */
    public function acquireLock($name, $timeout = null)
    {
        $blocking = $timeout === null;
        $start = microtime(true);
        $end = $start + $timeout / 1000;
        $locked = false;
        while (!(empty($this->locks[$name]) && $locked = $this->getLock($name, $blocking)) && ($blocking || ($timeout > 0 && microtime(true) < $end))) {
            usleep(static::USLEEP_TIME);
        }

        if ($locked) {
            $this->locks[$name] = true;

            return true;
        }

        return false;
    }

    /**
     * @param  string $name
     * @param  bool   $blocking If lock provider supports blocking then you can pass this param through,
     *                          otherwise, ignore this variable, default blocking method will be used.
     * @return bool
     */
    abstract protected function getLock($name, $blocking);

    /**
     * Information returned by this method allow to track down process which acquired lock
     * .
     * @return array
     */
    protected function getLockInformation()
    {
        return $this->lockInformationProvider->getLockInformation();
    }

    /**
     * @return LockInformationProviderInterface
     */
    public function getLockInformationProvider()
    {
        return $this->lockInformationProvider;
    }

    /**
     * @param LockInformationProviderInterface $lockInformationProvider
     */
    public function setLockInformationProvider($lockInformationProvider)
    {
        $this->lockInformationProvider = $lockInformationProvider;
    }
}
