# syntax = edrevo/dockerfile-plus
INCLUDE+ ../src/54/Dockerfile
INCLUDE+ ../src/common/shell.Dockerfile

RUN apt install libldb-dev libldap2-dev -y \
    && ln -fs /usr/lib/x86_64-linux-gnu/libldap.so /usr/lib/ \
    && docker-php-ext-install ldap \
    && docker-php-ext-enable ldap

# Run the user specified commands
ARG EXTRA_CMDS="echo 'None to run'"
RUN eval $EXTRA_CMDS

# Custom PHP.ini Config
COPY ./conf/.gitkeep ./conf/custom.ini* /usr/local/etc/php/conf.d/zzb-custom.ini

COPY ../src/conf/docker-php-entrypoint /usr/bin/docker-php-entrypoint
RUN chmod +x /usr/bin/docker-php-entrypoint

ENTRYPOINT []
CMD ["/usr/bin/supervisord"]
