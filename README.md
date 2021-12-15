[![License](https://img.shields.io/github/license/arvenil/ninja-mutex?color=informational)](http://opensource.org/licenses/MIT)
[![PHP](https://img.shields.io/packagist/php-v/arvenil/ninja-mutex)](https://packagist.org/packages/arvenil/ninja-mutex)
[![Version](https://img.shields.io/github/v/release/arvenil/ninja-mutex)](https://github.com/arvenil/ninja-mutex/releases/latest)

[![Build](https://github.com/arvenil/ninja-mutex/workflows/PHP/badge.svg)](https://github.com/arvenil/ninja-mutex/actions?query=workflow%3APHP)

[![Coverage](https://img.shields.io/scrutinizer/coverage/g/arvenil/ninja-mutex?color=success)](https://scrutinizer-ci.com/g/arvenil/ninja-mutex/?branch=master)
[![Quality](https://img.shields.io/scrutinizer/quality/g/arvenil/ninja-mutex?color=success&label=quality)](https://scrutinizer-ci.com/g/arvenil/ninja-mutex/?branch=master)
[![Maintainability](https://img.shields.io/codeclimate/maintainability-percentage/arvenil/ninja-mutex?color=success)](https://codeclimate.com/github/arvenil/ninja-mutex)
[![Grade](https://img.shields.io/symfony/i/grade/15c5c748-f8d8-4b56-b536-a29a151aac6c?color=success)](https://insight.symfony.com/projects/15c5c748-f8d8-4b56-b536-a29a151aac6c)
[![Total Downloads](https://img.shields.io/packagist/dt/arvenil/ninja-mutex.svg)](https://packagist.org/packages/arvenil/ninja-mutex)

## About

ninja-mutex is a simple to use mutex implementation for php. It supports different adapters (flock, memcache, mysql, redis, ...) so you can set it up as you wish. All adapters (if set up properly) can be used in multi server environment - in other words lock is shared between web servers.

## Usage

### Mutex

First you need to choose an adapter and setup it properly. For example if you choose flock implementation first you need to set up NFS filesystem and mount it on web servers. In this example we will choose memcache adapter:

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

If you want to use multiple mutexes in your project then MutexFabric is the right solution. Set up lock implementor once, and you can use as many mutexes as you want!

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

Add dependency to your project:

    php composer.phar require arvenil/ninja-mutex:*

## Running tests

Tests require vfsStream to work. To install it, simply run in project dir:

    wget -nc http://getcomposer.org/composer.phar && php composer.phar install --dev

To run tests type in a console:

    vendor/bin/phpunit

## Something doesn't work

Feel free to fork project, fix bugs and finally request for pull
