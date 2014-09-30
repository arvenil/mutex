#!/bin/sh

install_memcached() {
    if [ $(expr "${TRAVIS_PHP_VERSION}" "!=" "hhvm") -eq 1 ]; then
        echo "extension=memcached.so" >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini
    fi

    return $?
}

install_memcached > ~/memcached.log || ( echo "=== MEMCACHED INSTALL FAILED ==="; cat ~/memcached.log; exit 1 )
