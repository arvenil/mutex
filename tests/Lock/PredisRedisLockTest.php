<?php

namespace NinjaMutex\Tests\Lock;

use NinjaMutex\Lock\PredisRedisLock;
use NinjaMutex\Mutex;
use NinjaMutex\Tests\Mock\MockPredisClient;

class PredisRedisLockTest extends \NinjaMutex\Tests\AbstractTest
{
    protected function createPredisClient()
    {
        return new MockPredisClient();
    }

    protected function createLock($predisClient)
    {
        return new PredisRedisLock($predisClient);
    }

    public function testAcquireLock()
    {
        $predis = $this->createPredisClient();
        $lock = $this->createLock($predis);
        $mutex = new Mutex('very-critical-stuff', $lock);
        $this->assertTrue($mutex->acquireLock());
    }

    public function testAcquireLockFails()
    {
        $predis = $this->createPredisClient();

        // Acquire lock in 1st instance - should succeed
        $lock = $this->createLock($predis);
        $mutex = new Mutex('very-critical-stuff', $lock);
        $this->assertTrue($mutex->acquireLock());

        // Acquire lock in 2nd instance - should fail instantly because 0 timeout
        $lock2 = $this->createLock($predis);
        $mutex2 = new Mutex('very-critical-stuff', $lock2);
        $this->assertFalse($mutex2->acquireLock(0));
    }

    public function testAcquireLockSucceedsAfterReleased()
    {
        $predis = $this->createPredisClient();

        // Acquire lock in 1st instance - should succeed
        $lock = $this->createLock($predis);
        $mutex = new Mutex('very-critical-stuff', $lock);
        $this->assertTrue($mutex->acquireLock());

        $this->assertTrue($mutex->releaseLock());

        // Acquire lock in 2nd instance - should succeed because 1st lock had been released
        $lock2 = $this->createLock($predis);
        $mutex2 = new Mutex('very-critical-stuff', $lock2);
        $this->assertTrue($mutex2->acquireLock(0));
    }

    public function testAcquireLockSucceedsAfterTimeout()
    {
        $predis = $this->createPredisClient();

        // Acquire lock in 1st instance - should succeed
        $lock = $this->createLock($predis);
        $lock->setExpiration(2);
        $mutex = new Mutex('very-critical-stuff', $lock);
        $this->assertTrue($mutex->acquireLock());

        // Acquire lock in 2nd instance - should succeed after 2 seconds
        $lock2 = $this->createLock($predis);
        $mutex2 = new Mutex('very-critical-stuff', $lock2);
        $this->assertTrue($mutex2->acquireLock());
    }
}
