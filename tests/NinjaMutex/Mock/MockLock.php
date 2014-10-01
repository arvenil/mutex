<?php
/**
 * This file is part of ninja-mutex.
 *
 * (C) Kamil Dziedzic <arvenil@klecza.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace NinjaMutex\Mock;

use NinjaMutex\Lock\LockInterface;

/**
 * Mock to mimic Lock functionality
 *
 * @author Kamil Dziedzic <arvenil@klecza.pl>
 */
class MockLock implements LockInterface
{
    /**
     * Lock counter to protect against recursive deadlock
     *
     * @var integer
     */
    protected $counter = 0;

    /**
     * Whether the service is available
     * @var boolean
     */
    protected $available = true;

    /**
     * @param  string   $name
     * @param  null|int $timeout
     * @return bool
     */
    public function acquireLock($name, $timeout = null)
    {
        if (!$this->available) {
            return false;
        }
        $this->counter++;

        return true;
    }

    /**
     * @param $name
     * @return bool
     */
    public function releaseLock($name)
    {
        if (!$this->available) {
            return false;
        }
        if ($this->counter > 0) {
            $this->counter--;
            return true;
        }

        return false;
    }

    /**
     * @param $name
     * @return bool
     */
    public function isLocked($name)
    {
        if (!$this->available) {
            return false;
        }
        if ($this->counter > 0) {
            return true;
        }

        return false;
    }

    /**
     * @param bool $available
     */
    public function setAvailable($available)
    {
        $this->available = (bool) $available;
    }
}
