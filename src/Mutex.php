<?php
/**
 * This file is part of ninja-mutex.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package  Arvenil\Ninja\Mutex
 */
namespace Arvenil\Ninja\Mutex;

/**
 * Mutex
 *
 * @author Kamil Dziedzic <arvenil@klecza.pl>
 */
class Mutex {
    /**
     * Is mutex acquired?
     * 
     * @var bool
     */
    protected $acquired = false;

    /**
     * Lock implementor
     *
     * @var LockInterface
     */
    protected $lockImplementor;

    /**
     * Name of lock
     * 
     * @var string
     */
    protected $name;

    /**
     * Lock counter to protect against recursive deadlock
     * 
     * @var integer 
     */
    protected $counter = 0;

    public function __construct($name, LockInterface $lockImplementor) {
        $this->name = $name;
        $this->lockImplementor = $lockImplementor;
    }

    public function acquireLock($timeout = null) {
        if ($this->counter > 0 || $this->lockImplementor->acquireLock($this->name, $timeout)) {
            $this->counter++;
            return $this->acquired = true;
        }

        return false;
    }

    public function releaseLock() {
        if ($this->acquired) {
            if ($this->counter > 1) {
                $this->counter--;
                return true;
            }

            return !($this->acquired = !$this->lockImplementor->releaseLock($this->name));
        }

        return false;
    }

    public function __destruct() {
        // If we acquired lock then we should release it
        while ($this->acquired) {
            $this->releaseLock();
        }
    }

    /**
     * Check if Mutex is acquired
     *
     * @return type
     */
    public function isAcquired() {
        return $this->acquired;
    }

    public function isLocked() {
        return $this->lockImplementor->isLocked($this->name);
    }
}
