#!/bin/sh

install_memcached() {
    if [ $(expr "${TRAVIS_PHP_VERSION}" ">=" "5.5") -eq 1 ]; then
        MEMCACHED_VERSION="2.1.0"
        wget "http://pecl.php.net/get/memcached-${MEMCACHED_VERSION}.tgz" &&
        tar -zxf "memcached-${MEMCACHED_VERSION}.tgz" &&
        sh -c "cd memcached-${MEMCACHED_VERSION} && phpize && ./configure --enable-memcached && make && sudo make install"
    fi

    echo "extension=memcached.so" >> `php --ini | grep "Loaded Configuration" | sed -e "s/.*:\s*//"`

    return $?
}

install_memcached > ~/memcached.log || ( echo "=== MEMCACHED BUILD FAILED ==="; cat ~/memcached.log )
