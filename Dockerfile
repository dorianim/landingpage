FROM php:7.4.3-apache

RUN apt-get update && \
    apt-get install -y \
        zlib1g-dev libpng-dev libzip-dev libldap2-dev supervisor libyaml-dev
RUN docker-php-ext-install mysqli pdo pdo_mysql gd zip ldap
RUN pecl channel-update pecl.php.net
RUN pecl install yaml && docker-php-ext-enable yaml
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" && \
    php composer-setup.php && \
    php -r "unlink('composer-setup.php');" && \
    mv composer.phar /usr/local/bin/composer

RUN ln -s /etc/apache2/mods-available/ssl.load  /etc/apache2/mods-enabled/ssl.load
RUN ln -s /etc/apache2/mods-available/rewrite.load  /etc/apache2/mods-enabled/rewrite.load
RUN ln -s /etc/apache2/mods-available/actions.load  /etc/apache2/mods-enabled/actions.load

RUN rm -r /etc/apache2/sites-enabled
COPY conf/apache.conf /etc/apache2/sites-enabled/0000-landingpage.conf
COPY src /var/www/landingpage
COPY conf/supervisord.conf /etc/supervisor/conf.d/supervisord.conf 
COPY docker-entrypoint.sh /entrypoint.sh
COPY conf/php-log.ini $PHP_INI_DIR/conf.d/

RUN cd /var/www/landingpage && COMPOSER_ALLOW_SUPERUSER=1 composer install --no-dev

ENTRYPOINT ["/entrypoint.sh"]
