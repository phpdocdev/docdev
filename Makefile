#!make
include .env
export $(shell sed 's/=.*//' .env)

NAMES = $(shell ls ${DOCUMENTROOT} | grep -v / | tr '\n' " " | sed 's/ /\.\l\o\c /g')

setup:
	$(shell cp .env.example .env)
	$(shell brew install mkcert)
ssl:
	$(shell mkcert -install)
	$(shell mkcert -cert-file ./cert/nginx.pem -key-file ./cert/nginx.key localhost 127.0.0.1 ::1 $(NAMES))
start:
	$(shell docker-compose up --quiet-pull --force-recreate --build -V -d)