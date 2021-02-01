<?php
/**
 * This file is part of ninja-mutex.
 *
 * (C) Kamil Dziedzic <arvenil@klecza.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace NinjaMutex\Tests\Lock\Fabric;

use NinjaMutex\Lock\LockExpirationInterface;
use NinjaMutex\Lock\LockInterface;

/**
 * Lock Fabric interface
 *
 * @author Kamil Dziedzic <arvenil@klecza.pl>
 */
interface LockFabricWithExpirationInterface
{
    /**
     * @return LockInterface|LockExpirationInterface
     */
    public function create();
}
