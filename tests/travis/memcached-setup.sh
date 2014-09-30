#!/bin/sh

install_memcached() {
    echo "extension=memcached.so" >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini

    return $?
}

install_memcached > ~/memcached.log || ( echo "=== MEMCACHED INSTALL FAILED ==="; cat ~/memcached.log; exit 1 )
