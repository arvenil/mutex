#!/bin/sh

install_memcache() {
    if [ $(expr "${TRAVIS_PHP_VERSION}" "!=" "hhvm") -eq 1 ] && [ $(expr "${TRAVIS_PHP_VERSION}" "!=" "hhvm-nightly") -eq 1 ]; then
        echo "extension=memcache.so" >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini
    fi

    return $?
}

install_memcache > ~/memcache.log || ( echo "=== MEMCACHE INSTALL FAILED ==="; cat ~/memcache.log; exit 1 )
