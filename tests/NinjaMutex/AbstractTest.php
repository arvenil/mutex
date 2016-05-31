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

use NinjaMutex\Lock\DirectoryLock;
use NinjaMutex\Lock\FlockLock;
use NinjaMutex\Lock\MemcacheLock;
use NinjaMutex\Lock\MemcachedLock;
use NinjaMutex\Lock\MySqlLock;
use NinjaMutex\Lock\Fabric\MemcacheLockFabric;
use NinjaMutex\Lock\Fabric\MemcachedLockFabric;
use NinjaMutex\Mock\MockMemcache;
use NinjaMutex\Mock\MockMemcached;
use NinjaMutex\Mock\MockPredisClient;
use NinjaMutex\Mock\MockPhpRedisClient;
use NinjaMutex\Lock\PredisRedisLock;
use NinjaMutex\Lock\PhpRedisLock;
use Predis;
use \Redis;
use org\bovigo\vfs;

abstract class AbstractTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        vfs\vfsStreamWrapper::register();
        vfs\vfsStreamWrapper::setRoot(new vfs\vfsStreamDirectory('nfs'));
        mkdir('/tmp/mutex/');
    }

    public function tearDown()
    {
        foreach (new \DirectoryIterator(vfs\vfsStream::url('nfs')) as $file) {
            if (!$file->isDot()) {
                unlink($file->getPathname());
            }
        }
        rmdir(vfs\vfsStream::url('nfs'));

        foreach (new \DirectoryIterator('/tmp/mutex/') as $file) {
            if (!$file->isDot()) {
                unlink($file->getPathname());
            }
        }
        rmdir('/tmp/mutex/');
    }

    /**
     * @return array
     */
    public function lockImplementorProvider()
    {
        $memcacheLockFabric = new MemcacheLockFabric();
        $memcachedLockFabric = new MemcachedLockFabric();

        $data = array(
            // Just mocks
            $this->provideFlockMockLock(),
            $this->provideDirectoryMockLock(),
            $this->provideMemcacheMockLock(),
            $this->provideMemcachedMockLock(),
            $this->provideMysqlMockLock(),
            $this->providePredisRedisMockLock(),
            $this->providePhpRedisMockLock(),
            // Real locks
            $this->provideFlockLock(),
            $this->provideDirectoryLock(),
            array($memcacheLockFabric->create()),
            array($memcachedLockFabric->create()),
            $this->provideMysqlLock(),
            $this->providePredisRedisLock(),
            $this->providePhpRedisLock(),
        );

        return $data;
    }

    /**
     * @return array
     */
    public function lockImplementorWithBackendProvider()
    {
        $data = array(
            // Just mocks
            $this->provideMemcacheMockLock(),
            $this->provideMemcachedMockLock(),
            $this->providePredisRedisMockLock(),
            $this->providePhpRedisMockLock(),
        );

        return $data;
    }

    /**
     * @return array
     */
    public function lockFabricWithExpirationProvider()
    {
        $memcacheLockFabric = new MemcacheLockFabric();
        $memcachedLockFabric = new MemcachedLockFabric();

        $data = array(
            array($memcacheLockFabric),
            array($memcachedLockFabric),
        );

        return $data;
    }

    /**
     * @return array
     */
    protected function provideMemcacheMockLock()
    {
        $memcacheMock = new MockMemcache();

        return array(new MemcacheLock($memcacheMock), $memcacheMock);
    }

    /**
     * @return array
     */
    protected function provideMemcachedMockLock()
    {
        $memcachedMock = new MockMemcached();

        return array(new MemcachedLock($memcachedMock), $memcachedMock);
    }

    /**
     * @return array
     */
    protected function provideFlockMockLock()
    {
        return array(new FlockLock(vfs\vfsStream::url('nfs/')));
    }

    /**
     * @return array
     */
    protected function provideDirectoryMockLock()
    {
        return array(new DirectoryLock(vfs\vfsStream::url('nfs/')));
    }

    /**
     * @return array
     */
    protected function provideMysqlMockLock()
    {
        return array(new MySqlLock('', '', '', 3306, 'NinjaMutex\Mock\MockPDO'));
    }

    /**
     * @return array
     */
    protected function providePredisRedisMockLock()
    {
        $predisMock = new MockPredisClient();

        return array(new PredisRedisLock($predisMock), $predisMock);
    }

    /**
     * @return array
     */
    protected function providePhpRedisMockLock()
    {
        $predisMock = new MockPhpRedisClient();

        return array(new PhpRedisLock($predisMock), $predisMock);
    }

    /**
     * @return array
     */
    protected function provideFlockLock()
    {
        return array(new FlockLock('/tmp/mutex/'));
    }

    /**
     * @return array
     */
    protected function provideDirectoryLock()
    {
        return array(new DirectoryLock('/tmp/mutex/'));
    }

    /**
     * @return array
     */
    protected function provideMysqlLock()
    {
        return array(new MySqlLock('root', '', '127.0.0.1'));
    }

    /**
     * @return array
     */
    protected function providePredisRedisLock()
    {
        return array(new PredisRedisLock(new Predis\Client()));
    }

    /**
     * @return array
     */
    protected function providePhpRedisLock()
    {
        $redis = new Redis();
        $redis->connect('127.0.0.1', 6379);
        return array(new PhpRedisLock($redis));
    }
}
