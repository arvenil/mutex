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

use org\bovigo\vfs;

require_once 'autoload.php';

require_once 'Lock/LockAbstract.php';
require_once 'Lock/FlockLock.php';
require_once 'Lock/MemcacheLock.php';
require_once 'Lock/MySqlLock.php';
require_once 'MockMemcache.php';
require_once 'MockPDO.php';

abstract class AbstractMutexTest extends \PHPUnit_Framework_TestCase
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
        $data = array();
        $data[] = array(new FlockLock(vfs\vfsStream::url('nfs/')));
        $data[] = array(new MemcacheLock($memcacheMock));
        $mockMySqlLock = $this->getMock('Arvenil\Ninja\Mutex\MySqlLock', array('createPDO'), array('', '', ''));
        $mockMySqlLock->expects($this->any())->method('createPDO')->will(
            $this->returnCallback(function() {
                return new MockPDO('', '', '');
        }));
        $data[] = array($mockMySqlLock);

        // Real interfaces
        $memcache = new \Memcache();
        $memcache->connect('127.0.0.1', 11211);
        $data[] = array(new FlockLock('/tmp/mutex/'));
        $data[] = array(new MemcacheLock($memcache));
        $data[] = array(new MySqlLock('root', '', '127.0.0.1'));

        return $data;
    }
}