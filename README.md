## About

ninja-mutex is a simple to use mutex implementation for php. It supports different adapters (flock, memcache, mysql, ...) so you can setup it as you wish. All adapters (if set up properly) can be used in multi server environment - in other words lock is shared between web servers.

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

## Running tests

Tests require vfsStream to work. To install this simply run in project dir:

    wget -nc http://getcomposer.org/composer.phar && php composer.phar install

This should setup dependencies. To run tests type in console:

    phpunit --bootstrap ./tests/bootstrap.php --coverage-text tests

## Something doesn't work

[![Build Status](https://secure.travis-ci.org/arvenil/ninja-mutex.png?branch=master)](http://travis-ci.org/arvenil/ninja-mutex)

However if it still doesn't work for you then feel free to fork project, fix bugs and finally request for pull
