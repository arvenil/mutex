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


class BasicLockInformationProvider implements LockInformationProviderInterface
{
    /**
     * @inheritdoc
     */
    public function getLockInformation()
    {
        $pid = getmypid();
        $hostname = gethostname();

        $params = array();
        $params[] = $pid;
        $params[] = $hostname;

        return $params;
    }
}
