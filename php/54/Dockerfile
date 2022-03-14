# syntax = edrevo/dockerfile-plus
INCLUDE+ ../src/54/Dockerfile
INCLUDE+ ../src/common/shell.Dockerfile

# Run the user specified commands
ARG EXTRA_CMDS="echo 'None to run'"
RUN eval $EXTRA_CMDS

# Custom PHP.ini Config
COPY ./conf/.gitkeep ./conf/custom.ini* /usr/local/etc/php/conf.d/zzb-custom.ini

RUN pecl install xdebug-2.4.1
RUN docker-php-ext-enable xdebug

COPY ../src/conf/docker-php-entrypoint /usr/bin/docker-php-entrypoint
RUN chmod +x /usr/bin/docker-php-entrypoint

ENTRYPOINT []
CMD ["/usr/bin/supervisord"]
