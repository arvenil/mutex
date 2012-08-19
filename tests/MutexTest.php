<?php
/**
 * This file is part of Mutex.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package  Arvenil\Ninja\Mutex
 */
namespace Arvenil\Ninja\Mutex;

use org\bovigo\vfs;

require_once 'autoload.php';

require_once 'Mutex.php';
require_once 'Lock/LockAbstract.php';
require_once 'Lock/FlockLock.php';
require_once 'Lock/MemcacheLock.php';
require_once 'Lock/MySqlLock.php';
require_once 'MockMemcache.php';
require_once 'MockPDO.php';

class ArvenilNinjaMutex_MutexTest extends \PHPUnit_Framework_TestCase {
    public function setUp() {
        vfs\vfsStreamWrapper::register();
        vfs\vfsStreamWrapper::setRoot(new vfs\vfsStreamDirectory('nfs'));
    }

    public function tearDown() {
        foreach (new \DirectoryIterator(vfs\vfsStream::url('nfs')) as $file) {
            if (!$file->isDot()) {
                unlink($file->getPathname());
            }
        }
    }

    public function lockImplementorProvider() {
        $flock    = new FlockLock(vfs\vfsStream::url('nfs/'));

        $memcacheMock = new MockMemcache();
        $memcache = new MemcacheLock($memcacheMock);
        
        $pdoMock  = new MockPDO();
        $mysql    = new MySqlLock($pdoMock);

        $data = array();
        $data[] = array($flock);
        $data[] = array($memcache);
        $data[] = array($mysql);

        return $data;
    }

    /**
     * @dataProvider lockimplementorProvider
     * @param LockInterface $lockimplementor
     */
    public function testAquireAndReleaseLock(LockInterface $lockimplementor) {
        $mutex = new Mutex('forfiter', $lockimplementor);

        $this->assertTrue($mutex->aquireLock(0));
        $this->assertTrue($mutex->isAcquired());
        $this->assertTrue($mutex->isLocked());

        $this->assertTrue($mutex->releaseLock());
        $this->assertFalse($mutex->isAcquired());
        $this->assertFalse($mutex->isLocked());
    }

    /**
     * @dataProvider lockimplementorProvider
     * @param LockInterface $lockimplementor
     */
    public function testUnableToAquireSelfOwnedLock(LockInterface $lockimplementor) {
        $mutex = new Mutex('forfiter', $lockimplementor);
        $mutex->aquireLock(0);

        // Lock is already aquired by this mutex,
        // so we are unable to gain it again
        $this->assertFalse($mutex->aquireLock(0));

        // Lock should be still acquired
        $this->assertTrue($mutex->isAcquired());
        $this->assertTrue($mutex->isLocked());
    }

    /**
     * @dataProvider lockimplementorProvider
     * @param LockInterface $lockimplementor
     */
    public function testUnableToAquireLockHeldByOtherLock(LockInterface $lockimplementor) {
        $mutex1 = new Mutex('forfiter', $lockimplementor);
        $mutex1->aquireLock(0);

        $mutex = new Mutex('forfiter', $lockimplementor);

        // We don't acquire lock
        $this->assertFalse($mutex->isAcquired());

        // But it's held by other process
        $this->assertTrue($mutex->isLocked());

        // So we should be unable to aquire lock
        $this->assertFalse($mutex->aquireLock(0));
    }

    /**
     * @dataProvider lockimplementorProvider
     * @param LockInterface $lockimplementor
     */
    public function testUnableToReleaseLockHeldByOtherLock(LockInterface $lockimplementor) {
        $mutex1 = new Mutex('forfiter', $lockimplementor);
        $mutex1->aquireLock(0);

        $mutex = new Mutex('forfiter', $lockimplementor);

        // We don't acquire lock
        $this->assertFalse($mutex->isAcquired());

        // But it's held by other process
        $this->assertTrue($mutex->isLocked());

        // So we should be unable to release lock
        $this->assertFalse($mutex->releaseLock());
    }

    /**
     * @dataProvider lockimplementorProvider
     * @param LockInterface $lockimplementor
     */
    public function testAquireLockTimeout(LockInterface $lockimplementor) {
        $mutex1 = new Mutex('forfiter', $lockimplementor);
        $mutex1->aquireLock(0);

        $mutex = new Mutex('forfiter', $lockimplementor);
        $sleep = LockAbstract::USLEEP_TIME;

        $time = microtime(true)*1000;
        $mutex->aquireLock($sleep);
        $this->assertLessThanOrEqual(microtime(true)*1000, $time+$sleep);
    }

    /**
     * @dataProvider lockimplementorProvider
     * @param LockInterface $lockimplementor
     */
    public function testAquireLockWithTimeoutImmiedietly(LockInterface $lockimplementor) {
        $mutex = new Mutex('forfiter', $lockimplementor);
        $sleep = LockAbstract::USLEEP_TIME;

        $time = microtime(true)*1000;
        $mutex->aquireLock($sleep);
        $this->assertGreaterThan(microtime(true)*1000, $time+$sleep);
    }
}