<?php
/**
 * This file is part of ninja-mutex.
 *
 * (C) Kamil Dziedzic <arvenil@klecza.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace NinjaMutex\Tests;

use NinjaMutex\Lock\DirectoryLock;
use NinjaMutex\Lock\FlockLock;
use NinjaMutex\Lock\MemcachedLock;
use NinjaMutex\Lock\MemcacheLock;
use NinjaMutex\Lock\MySQLPDOLock;
use NinjaMutex\Lock\PredisRedisLock;
use NinjaMutex\Lock\PhpRedisLock;
use NinjaMutex\Tests\Lock\Fabric\MemcachedLockFabric;
use NinjaMutex\Tests\Lock\Fabric\MemcacheLockFabric;
use NinjaMutex\Tests\Mock\MockMemcache;
use NinjaMutex\Tests\Mock\MockMemcached;
use NinjaMutex\Tests\Mock\MockPhpRedisClient;
use NinjaMutex\Tests\Mock\MockPredisClient;
use org\bovigo\vfs;
use Predis;
use Redis;
use Memcache;

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
        $memcachedLockFabric = new MemcachedLockFabric();

        $data = array(
            // Just mocks
            $this->provideFlockMockLock(),
            $this->provideDirectoryMockLock(),
            $this->provideMemcachedMockLock(),
            $this->provideMysqlMockLock(),
            $this->providePredisRedisMockLock(),
            $this->providePhpRedisMockLock(),
            // Real locks
            $this->provideFlockLock(),
            $this->provideDirectoryLock(),
            array($memcachedLockFabric->create()),
            $this->provideMysqlLock(),
            $this->providePredisRedisLock(),
            $this->providePhpRedisLock(),
        );

        if (class_exists("Memcache")) {
            array_push($data, $this->provideMemcacheMockLock());

            $memcacheLockFabric = new MemcacheLockFabric();
            array_push($data, array($memcacheLockFabric->create()));
        }

        return $data;
    }

    /**
     * @return array
     */
    public function lockImplementorWithBackendProvider()
    {
        $data = array(
            // Just mocks
            $this->provideMemcachedMockLock(),
            $this->providePredisRedisMockLock(),
            $this->providePhpRedisMockLock(),
        );

        if (class_exists("Memcache")) {
            array_push($data, $this->provideMemcacheMockLock());
        }

        return $data;
    }

    /**
     * @return array
     */
    public function lockFabricWithExpirationProvider()
    {
        $memcachedLockFabric = new MemcachedLockFabric();

        $data = array(
            array($memcachedLockFabric),
        );

        if (class_exists("Memcache")) {
            $memcacheLockFabric = new MemcacheLockFabric();
            array_push($data, array($memcacheLockFabric));
        }

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
        return array(new MySQLPDOLock('', null, null, null, 'NinjaMutex\Tests\Mock\MockPDO'));
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
        return array(new MySQLPDOLock('mysql:', 'root', ''));
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
