#!/bin/sh

echo "extension=memcache.so" >> `php --ini | grep "Loaded Configuration" | sed -e "s/.*:\s*//"`
