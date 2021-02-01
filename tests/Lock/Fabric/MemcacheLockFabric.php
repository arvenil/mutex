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

use Memcache;
use NinjaMutex\Lock\MemcacheLock;

class MemcacheLockFabric implements LockFabricWithExpirationInterface
{
    /**
     * @return MemcacheLock
     */
    public function create()
    {
        $memcache = new Memcache();
        $host = getenv("MEMCACHED") ?: '127.0.0.1';
        $memcache->connect($host, 11211);

        return new MemcacheLock($memcache);
    }
}
