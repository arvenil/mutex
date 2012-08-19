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

/**
 * Lock implementor
 *
 * @author Kamil Dziedzic <arvenil@klecza.pl>
 */
interface LockInterface {

    /**
     * @return bool
     */
    public function acquireLock($name, $timeout = null);

    /**
     * @return bool
     */
    public function releaseLock($name);

    /**
     * @return bool
     */
    public function isLocked($name);
}
