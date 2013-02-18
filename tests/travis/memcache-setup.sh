#!/bin/sh

VERSION="2.2.7"

if [[ "$TRAVIS_PHP_VERSION" < "5.5" ]]; then
echo 'y' | pecl install memcache > ~/memcache.log || ( echo "=== MEMCACHE BUILD FAILED ==="; cat ~/memcache.log )
else
wget "http://pecl.php.net/get/memcache-$VERSION.tgz"
  tar -zxf "memcache-$VERSION.tgz"
  sh -c "cd memcache-$VERSION && phpize && ./configure --enable-memcache && make && sudo make install"
  echo "memcache.so" >> `php --ini | grep "Loaded Configuration" | sed -e "s/.*:\s*//"`
fi
