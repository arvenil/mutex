<?php
/**
 * This file is part of ninja-mutex.
 *
 * (C) Kamil Dziedzic <arvenil@klecza.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace NinjaMutex\Mock;

use Memcache;

/**
 * Mock memcache to mimic mutex functionality
 *
 * @author Kamil Dziedzic <arvenil@klecza.pl>
 */
class MockMemcache extends Memcache
{
    /**
     * @var string[]
     */
    protected static $data = array();

    public function __construct()
    {
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return bool
     */
    public function add($key, $value)
    {
        if (false === $this->get($key)) {
            self::$data[$key] = (string)$value;
            return true;
        }

        return false;
    }

    /**
     * @param string $key
     * @return array|bool|string
     */
    public function get($key)
    {
        if (!isset(self::$data[$key])) {
            return false;
        }

        return (string)self::$data[$key];
    }

    /**
     * @param string $key
     * @return bool|void
     */
    public function delete($key)
    {
        unset(self::$data[$key]);
        return true;
    }
}
