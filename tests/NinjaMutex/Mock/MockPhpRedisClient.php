<?php
/**
 * This file is part of ninja-mutex.
 *
 * (C) leo108 <root@leo108.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace NinjaMutex\Mock;

use \Redis;

/**
 * Mock \Redis to mimic PhpRedis functionality
 *
 * @author leo108 <root@leo108.com>
 */
class MockPhpRedisClient extends Redis implements PermanentServiceInterface
{
    /**
     * @var string[]
     */
    protected static $data = array();

    /**
     * Whether the service is available
     * @var boolean
     */
    protected $available = true;

    public function __construct()
    {
    }

    /**
     * @param  string $key
     * @param  mixed  $value
     * @return bool
     */
    public function setnx($key, $value)
    {
        if (!$this->available) {
            return false;
        }

        if (false === $this->get($key)) {
            self::$data[$key] = (string) $value;

            return true;
        }

        return false;
    }

    /**
     * @param  string $key
     * @return mixed
     */
    public function get($key)
    {
        if (!$this->available) {
            return false;
        }

        if (!isset(self::$data[$key])) {
            return false;
        }

        return (string) self::$data[$key];
    }

    /**
     * @param  string $key
     * @return bool
     */
    public function del($key)
    {
        if (!$this->available) {
            return false;
        }

        unset(self::$data[$key]);

        return true;
    }

    /**
     * @param bool $available
     */
    public function setAvailable($available)
    {
        $this->available = (bool) $available;
    }
}
