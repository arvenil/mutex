<?php
/**
 * This file is part of ninja-mutex.
 *
 * (C) Kamil Dziedzic <arvenil@klecza.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace NinjaMutex\Lock;

use Memcache;

/**
 * Lock implementor using Memcache
 *
 * @author Kamil Dziedzic <arvenil@klecza.pl>
 */
class MemcacheLock extends MemcacheLockAbstract
{
    /**
     * @param Memcache $memcache
     */
    public function __construct(Memcache $memcache)
    {
        parent::__construct($memcache);
    }
}
