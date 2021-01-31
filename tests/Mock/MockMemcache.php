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

use Memcache;

/**
 * Mock memcache to mimic mutex functionality
 *
 * @author Kamil Dziedzic <arvenil@klecza.pl>
 */
class MockMemcache extends Memcache implements PermanentServiceInterface
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
     * @param array|string $key
     * @param mixed $value
     * @param int|null $flags
     * @param int|null $exptime
     * @param int|null $cas
     * @return bool
     */
    public function add(array|string $key, mixed $value = null, int $flags = null, int $exptime = null, int $cas = null): bool
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
     * @param array|string $key
     * @param mixed|null $flags
     * @param mixed|null $cas
     * @return array|bool|string
     */
    public function get(array|string $key, mixed &$flags = null, mixed &$cas = null): mixed
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
     * @param array|string $key
     * @param int|null $exptime
     * @return array|string
     */
    public function delete(array|string $key, int $exptime = null): array|bool
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
