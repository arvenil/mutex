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

require_once 'Mutex.php';
require_once 'MutexException.php';

/**
 * Mutex fabric
 *
 * @author Kamil Dziedzic <arvenil@klecza.pl>
 */
class MutexFabric {
    protected $defaultLockImplementorName;
    protected $implementors = array();
    protected $mutexes = array();

    public function __construct($lockImplementorName, LockInterface $lockImplementor) {
        $this->registerLockImplementor($lockImplementorName, $lockImplementor);
    }

    /**
     *
     * @param string $name
     * @param LockInterface $implementor
     * @throws MutexException
     */
    public function registerLockImplementor($name, LockInterface $implementor) {
        if (isset($this->implementors[$name])) {
            throw new MutexException(sprintf('Name %s is already used', $name));
        }

        if (null === $this->defaultLockImplementorName) {
            $this->defaultLockImplementorName = $name;
        }

        $this->implementors[$name] = $implementor;
    }

    public function setDefaultLockImplementorName($registeredLockImplementorName) {
        $this->defaultLockImplementorName = $registeredLockImplementorName;
    }

    public function getDefaultLockImplementorName() {
        return $this->defaultLockImplementorName;
    }

    /**
     * Create and/or get mutex
     *
     * @param string $name
     * @param string $registeredMutexImplementatorName
     * @return Mutex
     */
    public function get($name, $registeredLockImplementorName = null) {
        if (null === $registeredLockImplementorName) {
            $registeredLockImplementorName = $this->getDefaultLockImplementorName();
        }

        if (!isset($this->mutexes[$registeredLockImplementorName][$name])) {
            $this->createMutex($name, $registeredLockImplementorName);
        }

        return $this->mutexes[$registeredLockImplementorName][$name];
    }

    protected function createMutex($name, $registeredLockImplementorName) {
        $this->mutexes[$registeredLockImplementorName][$name] = new Mutex($name, $this->implementors[$registeredLockImplementorName]);
    }
}
