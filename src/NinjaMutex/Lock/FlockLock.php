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

/**
 * Lock implementor using flock
 *
 * @author Kamil Dziedzic <arvenil@klecza.pl>
 */
class FlockLock extends LockAbstract
{
    protected $dirname;
    protected $files = array();
    protected $filesHasLock = array();

    /**
     * @param string $dirname
     */
    public function __construct($dirname)
    {
        parent::__construct();

        $this->dirname = $dirname;
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
        if (!$this->setupFileHandle($name)) {
            return false;
        }

        $options = LOCK_EX;

        // Check if we don't want to wait until lock is acquired
        if (null !== $timeout) {
            $options |= LOCK_NB;
        }

        $start = microtime(true);
        $end = $start + $timeout / 1000;
        $locked = false;
        while (!($locked = $this->getLock($name, $options)) && $timeout > 0 && microtime(true) < $end) {
            usleep(static::USLEEP_TIME);
        }

        return $locked;
    }

    /**
     * @param string $name
     * @param int $options
     * @return bool
     */
    protected function getLock($name, $options)
    {
        return empty($this->filesHasLock[$name]) && flock(
            $this->files[$name],
            $options
        ) && ($this->filesHasLock[$name] = true);
    }

    /**
     * Release lock
     *
     * @param string $name name of lock
     * @return bool
     */
    public function releaseLock($name)
    {
        if (isset($this->files[$name])) {
            flock($this->files[$name], LOCK_UN); // @todo Can LOCK_UN fail?
            $this->filesHasLock[$name] = false;
            return true;
        }

        return false;
    }

    /**
     * @param string $name
     * @return string
     */
    protected function getFilePath($name)
    {
        return $this->dirname . DIRECTORY_SEPARATOR . $name . '.lock';
    }

    /**
     * @param string $name
     * @return bool
     */
    private function setupFileHandle($name)
    {
        if (isset($this->files[$name])) {
            return true;
        }

        $file = fopen($this->getFilePath($name), 'c');
        if (false === $file) {
            return false;
        }

        $this->files[$name] = $file;
        return true;
    }

    public function __clone()
    {
        $this->files = array();
        $this->filesHasLock = array();
    }

    public function __destruct()
    {
        while (null !== $file = array_pop($this->files)) {
            fclose($file);
        }
    }

    /**
     * Check if lock is locked
     *
     * @param string $name name of lock
     * @return bool
     */
    public function isLocked($name)
    {
        if ($this->acquireLock($name, 0)) {
            return !$this->releaseLock($name);
        }

        return true;
    }
}
