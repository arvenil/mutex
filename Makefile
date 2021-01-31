osx:	                  ## Prepare dev env for osx.
	brew install composer
	brew install php
	#$(MAKE) ext-memcache
	#yes no | PHP_ZLIB_DIR=$(brew --prefix zlib) pecl install memcache
	yes no | PHP_ZLIB_DIR=/usr/local/opt/zlib pecl install memcache
	yes no | pecl install redis
	yes no | pecl install memcached
	yes no | pecl install xdebug
	brew install phpunit

#ext-memcache:
#	brew install zlib
#	pecl download memcache
#	tar --extract --file memcache-8.0.tgz
#	rm memcache-8.0.tgz
#	cd memcache-8.0 \
#	&& phpize \
#	&& ./configure \
#		--with-zlib-dir=/usr/local/opt/zlib \
#		--with-php-config=/usr/local/opt/php/bin/php-config \
#		--enable-memcache-session=yes \
#	&& make \
#	&& make install
#	rm -rf memcache-8.0

install:
	composer install

update:
	composer update

docker-test: docker-test-down
	docker-compose -f docker-compose.test.yml up --build -d
	docker-compose -f docker-compose.test.yml run sut
	$(MAKE) docker-test-down

docker-test-down:
	docker-compose -f docker-compose.test.yml down --volumes --remove-orphans

help: Makefile            ## Display this help message.
	@echo "Please use \`make <target>\` where <target> is one of:"
	@grep '^[a-zA-Z]' $(MAKEFILE_LIST) | \
		sort | \
		awk -F ':.*?## ' 'NF==2 {printf "  %-26s%s\n", $$1, $$2}'

.DEFAULT_GOAL := help
