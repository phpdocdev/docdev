# syntax = edrevo/dockerfile-plus
INCLUDE+ ../src/54/Dockerfile
INCLUDE+ ../src/common/zsh.Dockerfile


# Setup user (to stay in-sync with the host system's user)
ARG CUSTOM_USER_NAME
ARG CUSTOM_UID
ARG CUSTOM_GID
RUN groupadd -g ${CUSTOM_GID} ${CUSTOM_USER_NAME} \
  && useradd -m -u ${CUSTOM_UID} -g ${CUSTOM_USER_NAME} -G sudo -s /usr/bin/zsh ${CUSTOM_USER_NAME} \
  && passwd -d ${CUSTOM_USER_NAME} \
  && echo "${CUSTOM_USER_NAME} ALL=(ALL) NOPASSWD:ALL" >> /etc/sudoers

# Aliases
RUN echo "alias artisan='php artisan'" >> /home/${CUSTOM_USER_NAME}/.bashrc \
  && echo "alias magento='php bin/magento'" >> /home/${CUSTOM_USER_NAME}/.bashrc

# Run the user specified commands
ARG EXTRA_CMDS="echo 'None to run'"
RUN eval $EXTRA_CMDS

# Custom PHP.ini Config
COPY ./conf/.gitkeep ./conf/custom.ini* /usr/local/etc/php/conf.d/zzb-custom.ini

# Update permissions for supervisor and cron
RUN chown ${CUSTOM_USER_NAME}:${CUSTOM_USER_NAME} /etc/supervisor/supervisord.pid \
  && chmod gu+rw /var/run \
  && chmod gu+s /usr/sbin/cron

# Copy and enable CRON/s
COPY ./56/Dockerfile ./56/custom_crontab* /root/
RUN /usr/bin/crontab -u ${CUSTOM_USER_NAME} /root/custom_crontab

RUN pecl install xdebug-2.4.1
RUN docker-php-ext-enable xdebug

COPY ../src/conf/docker-php-entrypoint /usr/bin/docker-php-entrypoint
RUN chmod +x /usr/bin/docker-php-entrypoint

ENTRYPOINT []
CMD ["/usr/bin/supervisord"]
