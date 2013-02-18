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

require_once 'AbstractTest.php';

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
}