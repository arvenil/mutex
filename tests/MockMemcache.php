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

/**
 * Mock memcache to mimic mutex functionality
 *
 * @author Kamil Dziedzic <arvenil@klecza.pl>
 */
class MockMemcache extends \Memcache {
    /**
     * @var string[]
     */
    protected static $data = array();

    public function __construct () {}

    public function add($key, $value) {
        if (false === $this->get($key)) {
            self::$data[$key] = (string)$value;
            return true;
        }

        return false;
    }

    public function get($key) {
        if (!isset(self::$data[$key])) {
            return false;
        }

        return (string)self::$data[$key];
    }

    public function delete($key) {
        unset(self::$data[$key]);
        return true;
    }
}
