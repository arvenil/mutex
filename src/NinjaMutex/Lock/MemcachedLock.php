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

use Memcached;
use NinjaMutex\Lock\MemcacheLockAbstract;

/**
 * Lock implementor using Memcached
 *
 * @author Kamil Dziedzic <arvenil@klecza.pl>
 */
class MemcachedLock extends MemcacheLockAbstract
{
    /**
     * @param Memcached $memcached
     */
    public function __construct(Memcached $memcached)
    {
        parent::__construct($memcached);
    }
}
