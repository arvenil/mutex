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

use NinjaMutex\AbstractTest;

/**
 * Tests for Locks
 *
 * @author Kamil Dziedzic <arvenil@klecza.pl>
 */
class MemcachedLockTest extends AbstractTest
{
    public function testExpiration()
    {
        $name = "lock";
        $expiration = 2; // in seconds

        $lock1 = $this->createMemcachedLock($expiration);
        $lock2 = $this->createMemcachedLock($expiration);
        $this->assertTrue($lock1->acquireLock($name, 0));
        // We hope code was fast enough so $expiration time didn't pass yet and lock still should be held
        $this->assertFalse($lock2->acquireLock($name, 0));

        // Let's wait for lock to expire
        sleep($expiration);

        // Let's try again to lock
        $this->assertTrue($lock2->acquireLock($name, 0));
    }
}
