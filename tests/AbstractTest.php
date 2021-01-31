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
use NinjaMutex\Lock\PredisRedisLock;
use NinjaMutex\Lock\PhpRedisLock;
use NinjaMutex\Tests\Lock\Fabric\MemcachedLockFabric;
use NinjaMutex\Tests\Lock\Fabric\MemcacheLockFabric;
use NinjaMutex\Tests\Mock\MockMemcache;
use NinjaMutex\Tests\Mock\MockMemcached;
use NinjaMutex\Tests\Mock\MockPhpRedisClient;
use NinjaMutex\Tests\Mock\MockPredisClient;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamException;
use org\bovigo\vfs\vfsStreamWrapper;
use org\bovigo\vfs\vfsStreamDirectory;
use Predis;
use Redis;
use PHPUnit\Framework\TestCase;

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
    public function lockImplementorProvider(): array
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
     * @return array[]
     */
    public function lockImplementorWithBackendProvider(): array
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
    public function lockFabricWithExpirationProvider(): array
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
    protected function provideMemcacheMockLock(): array
    {
        $memcacheMock = new MockMemcache();

        return array(new MemcacheLock($memcacheMock), $memcacheMock);
    }

    /**
     * @return array
     */
    protected function provideMemcachedMockLock(): array
    {
        $memcachedMock = new MockMemcached();

        return array(new MemcachedLock($memcachedMock), $memcachedMock);
    }

    /**
     * @return FlockLock[]
     */
    protected function provideFlockMockLock(): array
    {
        return array(new FlockLock(vfsStream::url('nfs/')));
    }

    /**
     * @return DirectoryLock[]
     */
    protected function provideDirectoryMockLock(): array
    {
        return array(new DirectoryLock(vfsStream::url('nfs/')));
    }

    /**
     * @return MySQLPDOLock[]
     */
    protected function provideMysqlMockLock(): array
    {
        return array(new MySQLPDOLock('', null, null, null, 'NinjaMutex\Tests\Mock\MockPDO'));
    }

    /**
     * @return array
     */
    protected function providePredisRedisMockLock(): array
    {
        $predisMock = new MockPredisClient();

        return array(new PredisRedisLock($predisMock), $predisMock);
    }

    /**
     * @return array
     */
    protected function providePhpRedisMockLock(): array
    {
        $predisMock = new MockPhpRedisClient();

        return array(new PhpRedisLock($predisMock), $predisMock);
    }

    /**
     * @return FlockLock[]
     */
    protected function provideFlockLock(): array
    {
        return array(new FlockLock('/tmp/mutex/'));
    }

    /**
     * @return DirectoryLock[]
     */
    protected function provideDirectoryLock(): array
    {
        return array(new DirectoryLock('/tmp/mutex/'));
    }

    /**
     * @return MySQLPDOLock[]
     */
    protected function provideMysqlLock(): array
    {
        $host=getenv("MYSQL") ?: '127.0.0.1';
        return array(new MySQLPDOLock('mysql:host='.$host, 'root', ''));
    }

    /**
     * @return PredisRedisLock[]
     */
    protected function providePredisRedisLock(): array
    {
        $host=getenv("REDIS") ?: '127.0.0.1';
        return array(new PredisRedisLock(new Predis\Client([
            'host' => $host,
        ])));
    }

    /**
     * @return PhpRedisLock[]
     */
    protected function providePhpRedisLock(): array
    {
        $host=getenv("REDIS") ?: '127.0.0.1';
        $redis = new Redis();
        $redis->connect($host);
        return array(new PhpRedisLock($redis));
    }
}
