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

/**
 * Backend interface
 *
 * @author Kamil Dziedzic <arvenil@klecza.pl>
 */
interface PermanentServiceInterface
{

    /**
     * @param bool $available
     */
    public function setAvailable($available);
}
