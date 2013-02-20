#!/bin/sh

wget -nc http://getcomposer.org/composer.phar && php composer.phar install --dev --prefer-source
