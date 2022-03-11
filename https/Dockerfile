FROM alpine:3.6



ENV NGINX_VERSION 1.13.5

# Generate a cert
RUN apk add --no-cache openssl nginx gettext \
    && mkdir -p /etc/nginx/ssl/ \
    && mkdir -p /run/nginx/
# RUN openssl req -new -x509 -sha256 -newkey rsa:2048 -days 365 -nodes -out /etc/nginx/ssl/nginx.pem -keyout /etc/nginx/ssl/nginx.key -subj "/C=AU/ST=ALD/L=Brisbane/O=PVTL/OU=dev/CN=*.loc"

COPY nginx.conf.template /etc/nginx/nginx.conf.template
COPY entrypoint.sh .

EXPOSE 443
STOPSIGNAL SIGTERM

ENTRYPOINT sh entrypoint.sh
