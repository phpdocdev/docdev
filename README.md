# Getting Started

## Prerequisites

`MySQL` and `Redis` are both served from your host machine and are accessible via `mysql` and `redis` hostnames in your application. I reccomend using [DBNgin](https://dbngin.com) to host the services on your host machine.

## Commands
```
NAME: docdev

COMMANDS:
   init, i   Initialize configuration and install mkcert
   certs, c  Generate and install the certificates
   hosts     Generate hosts file, backed up and produced at at ./host. Will replace your system hostfile.
   start, s  Bring up the docker containers
   exec, e   Start docker container shell
   php, p    Change php version (requires "start" to rebuild). Valid values: 54, 56, 70, 71, 72, 73, 74
   help, h   Shows a list of commands or help for one command

GLOBAL OPTIONS:
   --help, -h  show help
```

## Command Usage

Create .env and install mkcert (brew)

`docdev init`

Create SSL certificate and CA

`docdev certs`

Start the containers and install certificates

`docdev start`

Shell into the php container

`docdev exec`

Change PHP version

`docdev php 74 && docdev start`

# Usage
## Environment

In your `.env` file, modify the value of `DOCUMENTROOT` to the directory containing your repositories. The folder names will represent the hostname to access the specific folder.
## Routing

In your `/etc/hosts` file, add `{myproject.loc}     127.0.0.1`
> `myproject` is the folder/project name.
> You may also want to install this to manage your host records in Mac system preferences: https://github.com/specialunderwear/Hosts.prefpane

## Project Configuration

Nginx is configured to serve from the `public` folder of each project. If you're dealing with a non-laravel project, set up a symlink: `ln -s web/ public`

## Access

You will access your projects via `https://myproject.loc`

> If you add any new projects, simply run `docdev certs && docdev start` to refresh the pathings.