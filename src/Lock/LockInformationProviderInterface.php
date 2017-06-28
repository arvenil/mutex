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

/**
 * Provides lock debugging information
 * @package NinjaMutex\Lock
 */
interface LockInformationProviderInterface
{
    /**
     * Gathers lock debug information
     * @return array
     */
    public function getLockInformation();
}
