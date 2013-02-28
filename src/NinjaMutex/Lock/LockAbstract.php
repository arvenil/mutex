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

use NinjaMutex\Lock\LockInterface;

/**
 * Abstract lock implementor
 *
 * @author Kamil Dziedzic <arvenil@klecza.pl>
 */
abstract class LockAbstract implements LockInterface
{
    const USLEEP_TIME = 100;

    /**
     * Information which allows to track down process which acquired lock
     *
     * @var array
     */
    protected $lockInformation = array();

    public function __construct()
    {
        $this->lockInformation = $this->generateLockInformation();
    }

    /**
     * Information generate by this method allow to track down process which acquired lock
     * .
     * By default it returns array with:
     * 1. pid
     * 2. server_ip
     * 3. server_name
     *
     * @return array
     */
    protected function generateLockInformation()
    {
        $pid = getmypid();
        $hostname = gethostname();
        $host = gethostbyname($hostname);

        // Compose data to one string
        $params = array();
        $params[] = $pid;
        $params[] = $host;
        $params[] = $hostname;

        return $params;
    }

    /**
     * Information returned by this method allow to track down process which acquired lock
     * .
     * @return array;
     */
    protected function getLockInformation()
    {
        return $this->lockInformation;
    }
}
