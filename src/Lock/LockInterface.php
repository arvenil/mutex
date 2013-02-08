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
 * Lock implementor
 *
 * @author Kamil Dziedzic <arvenil@klecza.pl>
 */
interface LockInterface {

    /**
     * @param $name
     * @param null|int $timeout
     * @return bool
     */
    public function acquireLock($name, $timeout = null);

    /**
     * @param $name
     * @return bool
     */
    public function releaseLock($name);

    /**
     * @param $name
     * @return bool
     */
    public function isLocked($name);
}
