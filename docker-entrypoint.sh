#!/bin/bash
set -e

if [ $# -eq 0 ]; then

	mkdir -p /data/{assets,themes,downloads}

        # Only copy default config when no old php config exists
        # to make sure we don't block the automated conversion
        if [ ! -e /data/config.yaml ] && [ ! -e /data/config.php ]; then
                cp /var/www/landingpage/config.example.yaml /data/config.yaml
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
