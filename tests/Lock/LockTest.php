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