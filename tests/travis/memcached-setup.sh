#!/bin/sh

install_memcached() {
    echo "extension=memcached.so" >> `php --ini | grep "Loaded Configuration" | sed -e "s/.*:\s*//"`

    return $?
}

install_memcached > ~/memcached.log || ( echo "=== MEMCACHED BUILD FAILED ==="; cat ~/memcached.log )
