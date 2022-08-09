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


class ResolvedHostnameLockInformationProvider extends BasicLockInformationProvider
{
    /**
     * Adds resolved host IP to the provided information
     * (WARNING! Using DNS queries at runtime may introduce significant delays to script execution, use with caution!)
     * @return array
     */
    public function getLockInformation()
    {
        $params = parent::getLockInformation();
        $params['hostIp'] = gethostbyname(gethostname());

        return $params;
    }
}
