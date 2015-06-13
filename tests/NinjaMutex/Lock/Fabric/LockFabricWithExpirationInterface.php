<?php
/**
 * This file is part of ninja-mutex.
 *
 * (C) Kamil Dziedzic <arvenil@klecza.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace NinjaMutex\Lock\Fabric;
use NinjaMutex\Lock\LockInterface;
use NinjaMutex\Lock\LockExpirationInterface;

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
