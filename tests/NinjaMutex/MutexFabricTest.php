<?php
/**
 * This file is part of ninja-mutex.
 *
 * (C) Kamil Dziedzic <arvenil@klecza.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace NinjaMutex;

use NinjaMutex\Lock\LockInterface;

/**
 * Tests for MutexFabric
 *
 * @author Kamil Dziedzic <arvenil@klecza.pl>
 */
class MutexFabricTest extends AbstractTest
{
    /**
     * @dataProvider lockImplementorProvider
     * @param LockInterface $lockImplementor
     */
    public function testIfInjectedImplementorIsSetAsDefault(LockInterface $lockImplementor)
    {
        $mutexFabric = new MutexFabric(get_class($lockImplementor), $lockImplementor);
        $this->assertSame($mutexFabric->getDefaultLockImplementorName(), get_class($lockImplementor));
    }

    /**
     * @dataProvider lockImplementorProvider
     * @param LockInterface $lockImplementor
     */
    public function testIfInjectedImplementorDefaultImplementorIsNotOverwritten(LockInterface $lockImplementor)
    {
        $mutexFabric = new MutexFabric(get_class($lockImplementor), $lockImplementor);
        $mutexFabric->registerLockImplementor(get_class($lockImplementor) . '_forfiter', $lockImplementor);
        $this->assertSame($mutexFabric->getDefaultLockImplementorName(), get_class($lockImplementor));
    }

    /**
     * @dataProvider lockImplementorProvider
     * @param LockInterface $lockImplementor
     */
    public function testRegisterNewImplementorAndSetIsAsDefault(LockInterface $lockImplementor)
    {
        $mutexFabric = new MutexFabric(get_class($lockImplementor), $lockImplementor);
        $mutexFabric->registerLockImplementor(get_class($lockImplementor) . '_forfiter', $lockImplementor);
        $mutexFabric->setDefaultLockImplementorName(get_class($lockImplementor) . '_forfiter');
        $this->assertSame($mutexFabric->getDefaultLockImplementorName(), get_class($lockImplementor) . '_forfiter');
    }

    /**
     * @dataProvider lockImplementorProvider
     * @expectedException \NinjaMutex\MutexException
     * @param LockInterface $lockImplementor
     */
    public function testThrowExceptionOnDuplicateImplementorName(LockInterface $lockImplementor)
    {
        $mutexFabric = new MutexFabric(get_class($lockImplementor), $lockImplementor);
        $mutexFabric->registerLockImplementor(get_class($lockImplementor), $lockImplementor);
    }

    /**
     * @dataProvider lockImplementorProvider
     * @param LockInterface $lockImplementor
     */
    public function testMutexCreationWithDefaultImplementor(LockInterface $lockImplementor)
    {
        $mutexFabric = new MutexFabric(get_class($lockImplementor), $lockImplementor);
        $this->assertInstanceOf('NinjaMutex\Mutex', $mutexFabric->get('lock'));
    }

    /**
     * @dataProvider lockImplementorProvider
     * @param LockInterface $lockImplementor
     */
    public function testMutexCreationWithSecondaryImplementor(LockInterface $lockImplementor)
    {
        $mutexFabric = new MutexFabric(get_class($lockImplementor), $lockImplementor);
        $mutexFabric->registerLockImplementor(get_class($lockImplementor) . '_forfiter', $lockImplementor);
        $this->assertInstanceOf(
            'NinjaMutex\Mutex',
            $mutexFabric->get('lock', get_class($lockImplementor) . '_forfiter')
        );
    }
}
