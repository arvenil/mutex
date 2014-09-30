#!/bin/sh

install_memcached() {
    MEMCACHED_VERSION="2.2.0"

    sudo apt-get -y -q --force-yes install libmemcached-dev pkg-config zlib1g-dev &&
    wget "http://pecl.php.net/get/memcached-${MEMCACHED_VERSION}.tgz" &&
    tar -zxf "memcached-${MEMCACHED_VERSION}.tgz" &&
    sh -c "cd memcached-${MEMCACHED_VERSION} && phpize && ./configure --disable-memcached-sasl --enable-memcached && make && make install"

    return $?
}

install_memcached > ~/memcached.log || ( echo "=== MEMCACHED BUILD FAILED ==="; cat ~/memcached.log; exit 1 )
