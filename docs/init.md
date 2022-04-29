# `docdev init`

Create .env, certificates, and modified hosts file then starts the containers.

<!-- TOC -->

- [docdev init](#docdev-init)
    - [Example](#example)
    - [Usage](#usage)

<!-- /TOC -->

## Example

`./docdev init --tld loc --php 74 --root /my/projects/dir/ --certs --hosts --start`

* `--tld loc` will be the TLD for the projects, making them accessible at `https://{project}.loc`
* `--php 74` is the initial PHP version and may be changed later.
* `--root /my/project/dir/` should be the parent folder of your git repositories. Any folder located within the root directory will represent a hostname. `/my/project/dir/repo1` will be accessible via `https://repo1.loc`.
* `--certs` will install `mkcert` and then generate a certificate that includes each hostname in your root project directory. If the root CA is not found in your keychain, it will be added and trusted automatically.
* `--hosts` will install `hostctl` and generate a new `/etc/host` file with all of your hostnames pointed to your localhost, which will be picked up by bindns. You will need to enter your password to replace your existing host file.
* `--start` will start the containers after the initialization is complete.

## Usage

```shell
USAGE:
   docdev init [command options] [arguments...]

OPTIONS:
  --tld value, -t value   TLD for project hostnames (default: "loc")
   --root value, -r value  Root directory containing your projects (default: "$HOME/repos/")
   --php value, -p value   Initial PHP version (default: "74")
   --certs                 Generate and install certificates (default: false)
   --hosts                 Generate hosts file (default: false)
   --start                 Start containers immediately (default: false)
   --dry-run, -d           Dry run (default: false)
   --help, -h              show help (default: false)
```
