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
 * Lock implementor using flock
 */
class FlockLock extends LockAbstract {
    protected $dirname;
    protected $files = array();

    public function __construct($dirname) {
        parent::__construct();

        $this->dirname = $dirname;
    }

    /**
     *
     * @param null|int $timeout 1. null if you want blocking lock
     *                          2. 0 if you want just lock and go
     *                          3. $timeout > 0 if you want to wait for lock some time (in miliseconds)
     * @return boolean
     */
    public function aquireLock($name, $timeout = null) {
        if (!$this->setupFileHandle($name)) {
            return false;
        }
        
        $options = LOCK_EX;

        // Check if we don't want to wait until lock is aquired
        if (null !== $timeout) {
            $options |= LOCK_NB;
        }

        $start = microtime(true);
        $end = $start + $timeout/1000;
        $locked = false;
        while (!($locked = flock($this->files[$name], $options)) && $timeout > 0 && microtime(true) < $end) {
            usleep(static::USLEEP_TIME);
        }
        
        if ($locked) {
            fwrite($this->files[$name], join("\t", $this->getLockInformation()));
        }
        
        return $locked;
    }

    public function releaseLock($name) {
//        ftruncate($this->file, 0); // @todo vfsStream doesn't work with truncate in php <= 5.3 so I can't test this
        flock($this->files[$name], LOCK_UN); // @todo Can LOCK_UN fail?
        return true;
    }

    protected function getFilePath($name) {
        return $this->dirname.DIRECTORY_SEPARATOR.$name.'.lock';
    }

    private function setupFileHandle($name) {
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

    public function __destruct() {
        while (null !== $file = array_pop($this->files)) {
            fclose($file);
        }
    }

    public function isLocked($name) {
        if ($this->aquireLock($name, 0)) {
            return !$this->releaseLock($name);
        }

        return true;
    }
}