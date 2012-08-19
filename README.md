## About

ninja-mutex is a simple to use mutex implementation for php. It supports different adapters (flock, memcache, mysql, ...) so you can setup it as you wish. All adapters (if setup them properly) can be used in multi server environment - in other words lock is shared between web servers.

## Usage

First you need to choose adapter and setup it properly. For example if you choose flock implementation first you need to setup NFS filesystem and mount it on web servers. In this example we will choose memcache adapter:

    <?php

    require_once 'Lock/MemcacheLock.php';
    require_once 'Mutex.php';

    use Arvenil\Ninja\Mutex\MemcacheLock;
    use Arvenil\Ninja\Mutex\Mutex;

    $memcache = new Memcache();
    $memcache->connect('127.0.0.1', 11211);
    $lock = new MemcacheLock($memcache);
    $mutex = new Mutex('very-critical-stuff', $lock);
    if ($mutex->acquireLock(1000)) {
        // Do some very critical stuff
    } else {
        throw new Exception('Unable to gain lock!');
    }

## Something doesn't work

SOA#1 [![Build Status](https://secure.travis-ci.org/arvenil/ninja-mutex.png?branch=master)](http://travis-ci.org/arvenil/ninja-mutex)

However if it still doesn't work for you then feel free to fork project, fix bugs and finally request for pull
