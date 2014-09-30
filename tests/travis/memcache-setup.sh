#!/bin/sh

install_memcache() {
    echo "extension=memcache.so" >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini

    return $?
}

install_memcache > ~/memcache.log || ( echo "=== MEMCACHE INSTALL FAILED ==="; cat ~/memcache.log; exit 1 )
