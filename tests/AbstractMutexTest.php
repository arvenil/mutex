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

require_once 'Lock/LockAbstract.php';
require_once 'Lock/FlockLock.php';
require_once 'Lock/MemcacheLock.php';
require_once 'Lock/MySqlLock.php';
require_once 'MockMemcache.php';
require_once 'MockPDO.php';

abstract class AbstractMutexTest extends \PHPUnit_Framework_TestCase {
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
}