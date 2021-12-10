#!make
include .env
export $(shell sed 's/=.*//' .env)

NAMES = $(shell ls ${DOCUMENTROOT} | grep -v / | tr '\n' " " | sed 's/ /\.\l\o\c /g')
NAMES += "${shell docker run -i --rm --network host ubuntu:latest hostname -I | sed 's/ .*//'}"

setup:
	$(shell cp .env.example .env)
	$(shell brew install mkcert)
ssl:
	$(shell mkcert -install)
	$(shell mkcert -cert-file ./cert/nginx.pem -key-file ./cert/nginx.key localhost 127.0.0.1 ::1 $(NAMES))
	$(shell cp -R "${shell  mkcert -CAROOT}"/ ./cert/)
start:
	$(shell docker-compose up --quiet-pull --force-recreate --build -V -d)
	docker exec php${PHPV} sudo cp -r /etc/ssl/cert/. /etc/ssl/certs/
	docker exec php${PHPV} sudo update-ca-certificates --fresh
exec:
	docker exec -ti php${PHPV} /bin/zsh