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

use Memcached;
use NinjaMutex\Lock\MemcachedLock;

class MemcachedLockFabric implements LockFabricWithExpirationInterface
{
    /**
     * @return MemcachedLock
     */
    public function create() {
        $memcached = new Memcached();
        $host=getenv("MEMCACHED") ?: '127.0.0.1';
        $memcached->addServer($host, 11211);

        return new MemcachedLock($memcached);
    }
}
