<?php
/**
 * This file is part of ninja-mutex.
 *
 * (C) Kamil Dziedzic <arvenil@klecza.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace NinjaMutex\Tests\Mock;

/**
 * Mock Predis\Client to mimic Predis functionality
 *
 * @author Kamil Dziedzic <arvenil@klecza.pl>
 */
class MockPredisClient implements PermanentServiceInterface
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
     * @param string $key
     * @param mixed $value
     * @return bool
     */
    public function setnx($key, $value)
    {
        if (!$this->available) {
            return false;
        }

        if (null === $this->get($key)) {
            self::$data[$key] = (string)$value;

            return true;
        }

        return false;
    }

    /**
     * @param string $key
     * @return string|null
     */
    public function get($key)
    {
        if (!$this->available) {
            return false;
        }

        if (!isset(self::$data[$key])) {
            return null;
        }

        return self::$data[$key];
    }

    /**
     * @param string[] $keys
     * @return bool
     */
    public function del(array $keys)
    {
        if (!$this->available) {
            return false;
        }

        foreach ($keys as $key) {
            unset(self::$data[$key]);
        }

        return true;
    }

    /**
     * @param bool $available
     */
    public function setAvailable($available)
    {
        $this->available = (bool)$available;
    }

    /**
     * @param      $key
     * @param      $value
     * @param null $expireResolution
     * @param null $expireTTL
     * @param null $flag
     *
     * @return bool
     */
    public function set($key, $value, $expireResolution = null, $expireTTL = null, $flag = null)
    {
        if (!$this->available) {
            return false;
        }

        self::$data[$key] = (string) $value;

        return true;
    }

    /**
     * @param $key
     * @param $value
     *
     * @return string|null
     */
    public function getset($key, $value)
    {
        if (!$this->available) {
            return false;
        }

        $oldValue = $this->get($key);

        $this->set($key, $value);

        return $oldValue;
    }
}
