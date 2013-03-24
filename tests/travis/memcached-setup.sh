#!/bin/sh

install_memcached() {
    echo "extension=memcached.so" >> `php --ini | grep "Loaded Configuration" | sed -e "s/.*:\s*//"`

    if [ $(expr "${TRAVIS_PHP_VERSION}" ">=" "5.5") -eq 1 ]; then
        MEMCACHED_VERSION="2.1.0"
        LIBMEMCACHED_VERSION="1.0.16"
        sudo apt-get remove libmemcached6 libmemcached-dev &&
        sudo apt-get install libevent-dev libcloog-ppl0 &&
        wget "https://launchpad.net/libmemcached/1.0/${LIBMEMCACHED_VERSION}/+download/libmemcached-${LIBMEMCACHED_VERSION}.tar.gz" &&
        tar xzf "libmemcached-${LIBMEMCACHED_VERSION}.tar.gz" &&
        sh -c "cd libmemcached-${LIBMEMCACHED_VERSION} && ./configure && make && sudo make install" &&
        wget "http://pecl.php.net/get/memcached-${MEMCACHED_VERSION}.tgz" &&
        tar -zxf "memcached-${MEMCACHED_VERSION}.tgz" &&
        sh -c "cd memcached-${MEMCACHED_VERSION} && phpize && ./configure --enable-memcached && make && sudo make install"
    fi

    return $?
}

install_memcached > ~/memcached.log || ( echo "=== MEMCACHED BUILD FAILED ==="; cat ~/memcached.log )
