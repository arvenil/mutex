#!/bin/sh

install_memcache() {
    sudo apt-get -y -q --force-yes install zlib1g-dev &&
    yes '' | pecl install memcache

    return $?
}

install_memcache > ~/memcache.log || ( echo "=== MEMCACHE BUILD FAILED ==="; cat ~/memcache.log; exit 1 )
