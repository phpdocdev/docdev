# syntax = edrevo/dockerfile-plus
FROM brandonkiefer/php-dev:5.6
INCLUDE+ ../src/common/ina.Dockerfile

# Run the user specified commands
ARG EXTRA_CMDS="echo 'None to run'"
RUN eval $EXTRA_CMDS

# Custom PHP.ini Config
COPY ./conf/.gitkeep ./conf/custom.ini* /usr/local/etc/php/conf.d/zzb-custom.ini

ENTRYPOINT []
CMD ["/usr/bin/supervisord"]
