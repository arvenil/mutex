## About

ninja-mutex is a simple to use mutex implementation for php. It supports different adapters (flock, memcache, mysql, ...) so you can setup it as you wish. All adapters (if set up properly) can be used in multi server environment - in other words lock is shared between web servers.

## Usage

### Mutex

First you need to choose adapter and setup it properly. For example if you choose flock implementation first you need to setup NFS filesystem and mount it on web servers. In this example we will choose memcache adapter:

```php
<?php
require 'vendor/autoload.php';

use NinjaMutex\Lock\MemcacheLock;
use NinjaMutex\Mutex;

$memcache = new Memcache();
$memcache->connect('127.0.0.1', 11211);
$lock = new MemcacheLock($memcache);
$mutex = new Mutex('very-critical-stuff', $lock);
if ($mutex->acquireLock(1000)) {
    // Do some very critical stuff

    // and release lock after you finish
    $mutex->releaseLock();
} else {
    throw new Exception('Unable to gain lock!');
}
```

### Mutex Fabric

If you want to use multiple mutexes in your project then MutexFabric is the right solution. You setup lock implementor once and you can use as many mutexes as you want!

```php
<?php
require 'vendor/autoload.php';

use NinjaMutex\Lock\MemcacheLock;
use NinjaMutex\MutexFabric;

$memcache = new Memcache();
$memcache->connect('127.0.0.1', 11211);
$lock = new MemcacheLock($memcache);
$mutexFabric = new MutexFabric('memcache', $lock);
if ($mutexFabric->get('very-critical-stuff')->acquireLock(1000)) {
    // Do some very critical stuff

    // and release lock after you finish
    $mutexFabric->get('very-critical-stuff')->releaseLock();
} else {
    throw new Exception('Unable to gain lock for very critical stuff!');
}

if ($mutexFabric->get('also-very-critical-stuff')->acquireLock(0)) {
    // Do some also very critical stuff

    // and release lock after you finish
    $mutexFabric->get('also-very-critical-stuff')->releaseLock();
} else {
    throw new Exception('Unable to gain lock for also very critical stuff!');
}
```

## Installation

### Composer

Download composer:

    wget -nc http://getcomposer.org/composer.phar

and add dependency to your project:

    php composer.phar require arvenil/ninja-mutex:*

## Running tests

Tests require vfsStream to work. To install it, simply run in project dir:

    wget -nc http://getcomposer.org/composer.phar && php composer.phar install --dev

To run tests type in console:

    phpunit

## Something doesn't work

[![Build Status](https://secure.travis-ci.org/arvenil/ninja-mutex.png?branch=master)](http://travis-ci.org/arvenil/ninja-mutex)

However if it still doesn't work for you then feel free to fork project, fix bugs and finally request for pull
