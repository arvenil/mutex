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
 * Lock implementor using flock
 *
 * @author Kamil Dziedzic <arvenil@klecza.pl>
 */
class FlockLock extends LockAbstract
{
    protected $dirname;
    protected $files = array();

    /**
     * @param string $dirname
     */
    public function __construct($dirname)
    {
        parent::__construct();

        $this->dirname = $dirname;
    }

    /**
     * @param  string $name
     * @param  bool   $blocking
     * @return bool
     */
    protected function getLock($name, $blocking)
    {
        if (!$this->setupFileHandle($name)) {
            return false;
        }

        $options = LOCK_EX;

        // Check if we don't want to wait until lock is acquired
        if (!$blocking) {
            $options |= LOCK_NB;
        }

        if (!flock($this->files[$name], $options)) {
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
        if (isset($this->files[$name]) && flock($this->files[$name], LOCK_UN)) {
            unset($this->locks[$name]);
            fclose($this->files[$name]);
            unset($this->files[$name]);

            return true;
        }

        return false;
    }

    /**
     * @param  string $name
     * @return string
     */
    protected function getFilePath($name)
    {
        return $this->dirname . DIRECTORY_SEPARATOR . $name . '.lock';
    }

    /**
     * @param  string $name
     * @return bool
     */
    protected function setupFileHandle($name)
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
        parent::__clone();
        $this->files = array();
    }

    /**
     * Check if lock is locked
     *
     * @param  string $name name of lock
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
