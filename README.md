# Getting Started

Create .env and install mkcert (brew)

`make setup`

Create SSL certificate and CA

`make ssl`

Start the containers and install certificates

`make start`

Shell into the php container

`make exec`


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

> If you add any new projects, simply run `make ssl && make start` to refresh the pathings.