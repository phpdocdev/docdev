# syntax = edrevo/dockerfile-plus
FROM php:5.6-fpm

ENV PATH /usr/local/go/bin:/root/.composer/vendor/bin:$PATH

RUN apt update && apt install -y --no-install-recommends \
    cron \
    git \
    gnupg \
    iputils-ping \
    less \
    libfreetype6-dev \
    libicu-dev \
    libjpeg62-turbo-dev \
    libmcrypt-dev \
    libmemcached-dev \
    libpng-dev \
    libvpx-dev \
    libxml2-dev \
    libxslt-dev \
    nano \
    net-tools \
    ssh \
    ssmtp \
    sudo \
    supervisor \
    unzip \
    zip \
  && yes '' | pecl install -f memcached-2.2.0 redis-4.3.0 xdebug-2.5.5 \
  && docker-php-ext-install -j$(nproc) bcmath calendar mysql intl mcrypt mysqli opcache pdo_mysql soap xsl zip\
  && docker-php-ext-enable mcrypt memcached redis xdebug\
  && apt clean \
  && rm -rf /var/lib/apt/lists/*

# Configure GD Library
RUN docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ --with-vpx-dir=/usr/include/ \
  && docker-php-ext-install -j$(nproc) gd

# Install Composer
RUN curl --silent --show-error https://getcomposer.org/installer | php \
  && mv composer.phar /usr/local/bin/composer \
  && composer clear-cache

# Install MailHog
RUN curl -L -o /usr/bin/mhsendmail https://github.com/mailhog/mhsendmail/releases/download/v0.2.0/mhsendmail_linux_amd64 \
  && chmod +x /usr/bin/mhsendmail

# Setup Crons
RUN touch /root/custom_crontab \
  && /usr/bin/crontab -u www-data /root/custom_crontab

# Update site's directory permissions
RUN chown -R www-data /var/www/

# Custom PHP.ini Config
COPY ./src/conf/custom.ini /usr/local/etc/php/conf.d/zza-custom.ini

# PHP-FPM and CRON supervisord config
COPY ./src/conf/supervisord.conf /etc/supervisor/supervisord.conf
RUN touch /etc/supervisor/supervisord.pid

ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN chmod +x /usr/local/bin/install-php-extensions && sync && \
    install-php-extensions ldap ioncube_loader

ARG PHPV=56
INCLUDE+ ./src/common/shell.Dockerfile

ENTRYPOINT []
CMD ["/usr/bin/supervisord"]
