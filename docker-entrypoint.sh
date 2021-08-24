#!/bin/bash
set -e

if [ $# -eq 0 ]; then

	mkdir -p /data/{assets,themes,downloads}

        if [ ! -e /data/config.php ]; then
                cp /var/www/landingpage/config.php.example /data/config.php
        fi

	rm -rf /var/www/landingpage/assets
	ln -s /data/assets /var/www/landingpage/assets

	/usr/bin/supervisord
fi

# first arg is `-f` or `--some-option`
if [ "${1#-}" != "$1" ]; then
        set -- php "$@"
fi

exec "$@"
