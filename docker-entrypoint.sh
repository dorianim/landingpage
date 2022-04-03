#!/bin/bash
set -e

if [ $# -eq 0 ]; then

	mkdir -p /data/{assets,themes,downloads}

	rm -rf /var/www/landingpage/assets
	ln -s /data/assets /var/www/landingpage/assets

        /var/www/landingpage/cli.php migrate
        if [ $? -ne 0 ]; then
                echo "Migration error (see above)!";
                exit 1
        fi

        if [ ! -e /data/config.yaml ] && [ ! -e /data/config.php ]; then
                cp /var/www/landingpage/config.example.yaml /data/config.yaml
        fi

	/usr/bin/supervisord
fi

# first arg is `-f` or `--some-option`
if [ "${1#-}" != "$1" ]; then
        set -- php "$@"
fi

exec "$@"
