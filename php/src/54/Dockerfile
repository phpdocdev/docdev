FROM php:5.4-fpm

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
    zip

RUN yes '' | pecl install -f memcached-2.2.0 redis-2.2.8
RUN docker-php-ext-install bcmath calendar mysql intl mcrypt mysqli mysql pdo_mysql soap xsl zip
RUN docker-php-ext-enable mcrypt memcached redis

# Configure GD Library
RUN docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ --with-vpx-dir=/usr/include/ \
  && docker-php-ext-install gd

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

RUN pecl install xdebug-2.4.1
RUN docker-php-ext-enable xdebug

ENTRYPOINT []
CMD ["/usr/bin/supervisord"]
