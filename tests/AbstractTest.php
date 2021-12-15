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

use DirectoryIterator;
use NinjaMutex\Lock\DirectoryLock;
use NinjaMutex\Lock\FlockLock;
use NinjaMutex\Lock\MemcachedLock;
use NinjaMutex\Lock\MemcacheLock;
use NinjaMutex\Lock\MySQLPDOLock;
use NinjaMutex\Lock\PhpRedisLock;
use NinjaMutex\Lock\PredisRedisLock;
use NinjaMutex\Tests\Lock\Fabric\MemcachedLockFabric;
use NinjaMutex\Tests\Lock\Fabric\MemcacheLockFabric;
use NinjaMutex\Tests\Mock\MockMemcache;
use NinjaMutex\Tests\Mock\MockMemcached;
use NinjaMutex\Tests\Mock\MockPhpRedisClient;
use NinjaMutex\Tests\Mock\MockPredisClient;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStreamException;
use org\bovigo\vfs\vfsStreamWrapper;
use PHPUnit\Framework\TestCase;
use Predis;
use Redis;

abstract class AbstractTest extends TestCase
{
    /**
     * @throws vfsStreamException
     */
    public function setUp(): void
    {
        vfsStreamWrapper::register();
        vfsStreamWrapper::setRoot(new vfsStreamDirectory('nfs'));
        mkdir('/tmp/mutex/');
    }

    public function tearDown(): void
    {
        foreach (new DirectoryIterator(vfsStream::url('nfs')) as $file) {
            if (!$file->isDot()) {
                unlink($file->getPathname());
            }
        }
        rmdir(vfsStream::url('nfs'));

        foreach (new DirectoryIterator('/tmp/mutex/') as $file) {
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
     * @return FlockLock[]
     */
    protected function provideFlockMockLock()
    {
        return array(new FlockLock(vfsStream::url('nfs/')));
    }

    /**
     * @return DirectoryLock[]
     */
    protected function provideDirectoryMockLock()
    {
        return array(new DirectoryLock(vfsStream::url('nfs/')));
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
     * @return MySQLPDOLock[]
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
     * @return FlockLock[]
     */
    protected function provideFlockLock()
    {
        return array(new FlockLock('/tmp/mutex/'));
    }

    /**
     * @return DirectoryLock[]
     */
    protected function provideDirectoryLock()
    {
        return array(new DirectoryLock('/tmp/mutex/'));
    }

    /**
     * @return MySQLPDOLock[]
     */
    protected function provideMysqlLock()
    {
        $host = getenv("MYSQL") ?: '127.0.0.1';
        return array(new MySQLPDOLock('mysql:host=' . $host, 'root', ''));
    }

    /**
     * @return PredisRedisLock[]
     */
    protected function providePredisRedisLock()
    {
        $host = getenv("REDIS") ?: '127.0.0.1';
        return array(new PredisRedisLock(new Predis\Client(array(
            'host' => $host,
        ))));
    }

    /**
     * @return PhpRedisLock[]
     */
    protected function providePhpRedisLock()
    {
        $host = getenv("REDIS") ?: '127.0.0.1';
        $redis = new Redis();
        $redis->connect($host);
        return array(new PhpRedisLock($redis));
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
     * @return array[]
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
     * @return Lock\Fabric\MemcachedLockFabric[][]
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
}
