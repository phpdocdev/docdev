# syntax = edrevo/dockerfile-plus
FROM brandonkiefer/php-dev:5.4
INCLUDE+ ../src/common/ina.Dockerfile

# Run the user specified commands
ARG EXTRA_CMDS="echo 'None to run'"
RUN eval $EXTRA_CMDS

# Custom PHP.ini Config
COPY ./conf/.gitkeep ./conf/custom.ini* /usr/local/etc/php/conf.d/zzb-custom.ini

COPY ../src/conf/docker-php-entrypoint /usr/bin/docker-php-entrypoint
RUN chmod +x /usr/bin/docker-php-entrypoint

ENTRYPOINT []
CMD ["/usr/bin/supervisord"]
