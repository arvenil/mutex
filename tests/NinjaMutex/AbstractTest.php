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

use Memcache;
use Memcached;
use NinjaMutex\Lock\FlockLock;
use NinjaMutex\Lock\MemcacheLock;
use NinjaMutex\Lock\MemcachedLock;
use NinjaMutex\Lock\MySqlLock;
use NinjaMutex\Mock\MockMemcache;
use NinjaMutex\Mock\MockMemcached;
use NinjaMutex\Mock\MockPDO;
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

    public function lockImplementorProvider()
    {
        // Just mocks
        $memcacheMock = new MockMemcache();
        $memcachedMock = new MockMemcached();
        $data = array();
        $data[] = array(new FlockLock(vfs\vfsStream::url('nfs/')));
        $data[] = array(new MemcacheLock($memcacheMock));
        $data[] = array(new MemcachedLock($memcachedMock));
        $data[] = array(new MySqlLock('', '', '', 'NinjaMutex\Mock\MockPDO'));

        // Real interfaces
        $memcache = new Memcache();
        $memcache->connect('127.0.0.1', 11211);
        $memcached = new Memcached();
        $memcached->addServer('127.0.0.1', 11211);
        $data[] = array(new FlockLock('/tmp/mutex/'));
        $data[] = array(new MemcachedLock($memcached));
        $data[] = array(new MemcacheLock($memcache));
        $data[] = array(new MySqlLock('root', '', '127.0.0.1'));

        return $data;
    }
}
