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
use NinjaMutex\Lock\Fabric\LockFabricWithExpirationInterface;
use NinjaMutex\Mock\PermanentServiceInterface;
use NinjaMutex\UnrecoverableMutexException;

/**
 * Tests for Locks
 *
 * @author Kamil Dziedzic <arvenil@klecza.pl>
 */
class LockTest extends AbstractTest
{
    /**
     * @dataProvider lockImplementorProvider
     * @param LockInterface $lockImplementor
     */
    public function testDisallowToAcquireSelfOwnedLock(LockInterface $lockImplementor)
    {
        $name = 'forfiter';
        $lockImplementor->acquireLock($name, 0);

        $this->assertFalse($lockImplementor->acquireLock($name, 0));

        $lockImplementor->releaseLock($name);
    }

    /**
     * @dataProvider lockImplementorProvider
     * @param LockInterface $lockImplementor
     */
    public function testDisallowToAcquireLockOwnedByOtherLockImplementor(LockInterface $lockImplementor)
    {
        $name = 'forfiter';
        $duplicateLockImplementor = clone $lockImplementor;
        $lockImplementor->acquireLock($name, 0);

        $this->assertFalse($duplicateLockImplementor->acquireLock($name, 0));

        $lockImplementor->releaseLock($name);
    }

    /**
     * @dataProvider lockImplementorProvider
     * @param LockInterface $lockImplementor
     */
    public function testDisallowLockImplementorToReleaseLockAcquiredByOtherImplementor(LockInterface $lockImplementor)
    {
        $name = 'forfiter';
        $lockImplementor->acquireLock($name, 0);

        $duplicateLockImplementor = clone $lockImplementor;
        $this->assertFalse($duplicateLockImplementor->releaseLock($name));

        $lockImplementor->releaseLock($name);
    }

    /**
     * @dataProvider lockImplementorProvider
     * @param LockInterface $lockImplementor
     */
    public function testIfLocksAreNotSharedBetweenImplementors(LockInterface $lockImplementor)
    {
        $name = 'forfiter';
        $lockImplementor->acquireLock($name, 0);

        $duplicateLockImplementor = clone $lockImplementor;
        $duplicateLockImplementor->releaseLock($name);
        $this->assertFalse($duplicateLockImplementor->acquireLock($name, 0));

        $lockImplementor->releaseLock($name);
    }

    /**
     * @dataProvider lockImplementorProvider
     * @param LockInterface $lockImplementor
     */
    public function testIfLockReleasedByOneImplementorCanBeAcquiredByOther(LockInterface $lockImplementor)
    {
        $name = 'forfiter';
        $lockImplementor->acquireLock($name, 0);
        $lockImplementor->releaseLock($name);

        $duplicateLockImplementor = clone $lockImplementor;
        $this->assertTrue($duplicateLockImplementor->acquireLock($name, 0));

        $duplicateLockImplementor->releaseLock($name);
    }

    /**
     * @dataProvider lockImplementorProvider
     * @param LockInterface $lockImplementor
     */
    public function testAcquireAndReleaseLock(LockInterface $lockImplementor)
    {
        $name = 'forfiter';
        $this->assertTrue($lockImplementor->acquireLock($name, 0));
        $this->assertTrue($lockImplementor->isLocked($name));
        $this->assertTrue($lockImplementor->releaseLock($name));
        $this->assertFalse($lockImplementor->isLocked($name));
    }

    /**
     * @dataProvider lockImplementorProvider
     * @param LockInterface $lockImplementor
     */
    public function testIfLockIsReleasedAfterLockImplementorIsDestroyed(LockInterface $lockImplementor)
    {
        $name = 'forfiter';
        $duplicateLockImplementor = clone $lockImplementor;
        $duplicateLockImplementor->acquireLock($name, 0);
        unset($duplicateLockImplementor);

        $this->assertTrue($lockImplementor->acquireLock($name, 0));

        $lockImplementor->releaseLock($name);
    }

    /**
     * @issue https://github.com/arvenil/ninja-mutex/pull/4
     * It's not working for hhvm, see below link to understand limitation
     * https://github.com/facebook/hhvm/blob/af329776c9f740cc1c8c4791f673ba5aa49042ce/hphp/doc/inconsistencies#L40-L45
     *
     * @dataProvider lockImplementorWithBackendProvider
     * @param LockInterface             $lockImplementor
     * @param PermanentServiceInterface $backend
     */
    public function testIfLockDestructorThrowsWhenBackendIsUnavailable(LockInterface $lockImplementor, PermanentServiceInterface $backend)
    {
        $name = "forfiter";

        $this->assertFalse($lockImplementor->isLocked($name));
        $this->assertTrue($lockImplementor->acquireLock($name, 0));
        $this->assertTrue($lockImplementor->isLocked($name));

        // make backend unavailable
        $backend->setAvailable(false);

        try {
            // explicit __destructor() call, should throw UnrecoverableMutexException
            $lockImplementor->__destruct();
        } catch (UnrecoverableMutexException $e) {
            // make backend available again
            $backend->setAvailable(true);
            // release lock
            $this->assertTrue($lockImplementor->releaseLock($name));
            $this->assertFalse($lockImplementor->releaseLock($name));
            $this->assertFalse($lockImplementor->isLocked($name));

            return;
        }

        $this->fail('An expected exception has not been raised.');
    }

    /**
     * @issue https://github.com/arvenil/ninja-mutex/issues/12
     * @medium Timeout for test increased to ~5s http://stackoverflow.com/a/10535787/916440
     *
     * @dataProvider lockFabricWithExpirationProvider
     * @param LockFabricWithExpirationInterface $lockFabricWithExpiration
     */
    public function testExpiration(LockFabricWithExpirationInterface $lockFabricWithExpiration)
    {
        $expiration = 2; // in seconds
        $name = "lockWithExpiration_" . uniqid();
        $lockImplementor = $lockFabricWithExpiration->create();
        $lockImplementorWithExpiration = $lockFabricWithExpiration->create();
        $lockImplementorWithExpiration->setExpiration($expiration);

        // Aquire lock on implementor with lock expiration
        $this->assertTrue($lockImplementorWithExpiration->acquireLock($name, 0));
        // We hope code was fast enough so $expiration time didn't pass yet and lock still should be held
        $this->assertFalse($lockImplementor->acquireLock($name, 0));

        // Let's wait for lock to expire
        sleep($expiration);

        // Let's try again to lock
        $this->assertTrue($lockImplementor->acquireLock($name, 0));

        // Cleanup
        $this->assertTrue($lockImplementor->releaseLock($name, 0));
        // Expired lock is unusable, we need to clean it's lock state or otherwise
        // it will invoke in __destruct Exception (php*) or Fatal Error (hhvm)
        $this->assertTrue($lockImplementorWithExpiration->clearLock($name, 0));
    }
}
