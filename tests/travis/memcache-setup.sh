#!/bin/sh

install_memcache() {
    echo "extension=memcache.so" >> `php --ini | grep "Loaded Configuration" | sed -e "s/.*:\s*//"`

    return $?
}

install_memcache > ~/memcache.log || ( echo "=== MEMCACHE BUILD FAILED ==="; cat ~/memcache.log )
