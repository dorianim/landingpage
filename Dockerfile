FROM php:7.4.3-apache

RUN apt-get update && \
    apt-get install -y \
        zlib1g-dev libpng-dev libzip-dev libldap2-dev supervisor
RUN docker-php-ext-install mysqli pdo pdo_mysql gd zip ldap

RUN ln -s /etc/apache2/mods-available/ssl.load  /etc/apache2/mods-enabled/ssl.load
RUN ln -s /etc/apache2/mods-available/rewrite.load  /etc/apache2/mods-enabled/rewrite.load
RUN ln -s /etc/apache2/mods-available/actions.load  /etc/apache2/mods-enabled/actions.load

RUN rm -r /etc/apache2/sites-enabled
COPY conf/apache.conf /etc/apache2/sites-enabled/0000-landingpage.conf
COPY src /var/www/landingpage
COPY conf/supervisord.conf /etc/supervisor/conf.d/supervisord.conf 
COPY docker-entrypoint.sh /entrypoint.sh
COPY conf/php-log.ini $PHP_INI_DIR/conf.d/

ENTRYPOINT ["/entrypoint.sh"]
