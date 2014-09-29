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

use Predis;

/**
 * Mock Predis\Client to mimic Predis functionality
 *
 * @author Kamil Dziedzic <arvenil@klecza.pl>
 */
class MockPredisClient extends Predis\Client implements PermanentServiceInterface
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

        if (null === $this->get($key)) {
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
            return null;
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
