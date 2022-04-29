<!-- TOC -->

- [Hosts](#hosts)
    - [Setup](#setup)
- [System-wide DNS](#system-wide-dns)
    - [Setup](#setup)
- [Docker VPN](#docker-vpn)
    - [Setup](#setup)
    - [Testing](#testing)
- [dnsmasqDN](#dnsmasqdn)
    - [Setup](#setup)
    - [Point macOS to the new server](#point-macos-to-the-new-server)
        - [Via GUI](#via-gui)
        - [Via Scripting](#via-scripting)
    - [Testing](#testing)
    - [Troubleshooting](#troubleshooting)
        - [Flush DNS caches](#flush-dns-caches)

<!-- /TOC -->

# Hosts

`docdev` has a built in command to generate a `/etc/hosts` file for routing all of your projects.

## Setup

1. Run `docdev hosts`
    - You may attempt a dry-run by appending the `-d` option. The original and modified hosts files will be placed in `$DOCDEV_PATH/data/hosts`

When new projects are added, just run `docdev hosts` again to update your hosts file.

# System-wide DNS

You also have the option to use the bind9 container as your system's DNS server, passing all hostname resolutions through the bind9 container instance.

## Setup

1. Add the env `DNS_FORWARDERS` to your `$DOCDEV_PATH/.env` in the following format, with the servers of your choice:
    - `DNS_FORWARDERS=9.9.9.9,8.8.4.4`
2. Add `127.0.0.1` as your primary nameserver
    - See: [Point macOS to the new server](#point-macos-to-the-new-server)

# Docker VPN

This method utlizes a OpenVPN and socat docker container on your host machine, allowing split DNS with minimal setup.

## Setup

1. Clone the [dns-vpn](https://github.com/phpdocdev/dns-vpn) repository:
   - `git clone https://github.com/phpdocdev/dns-vpn`
2. Find Docker's default address pool (`$DEFAULT_ADDRESS`):
   - `echo $(IFS=. read -r i1 i2 i3 i4 <<< $(docker system info --format '{{range .DefaultAddressPools }}{{.Base}}{{end}}') && echo $((i1)).$((i2)).$((i3)).$(((i4)+1)))`
3. Find your BIND IP from `$DOCDEV_PATH/docker-compose.yaml -> bind.ipv4_address` (`$BIND_ADDRESS`)
4. Update `dns-vpn/docker-compose.yml`:
   ```
    services
        proxy
            ...
            command: `TCP-LISTEN:13194,fork,reuseaddr TCP-CONNECT:$DEFAULT_ADDRESS`
            environment:
                tcpip: $DEFAULT_ADDRESS
        ...
        openvpn
            ...
            environment:
                bindip: $BIND_ADDRESS
   ```
5. Start the containers:
   - `docker-compose up -d`
6. Import `docdev.ovpn` to your VPN client and connect.

## Testing

1. Start a TCP dump
   - `sudo tcpdump -i en0 udp port 53`
2. ping `{myproject}.loc`
   - Verify no lookups appear in the tcpdump
3. ping `google.com`
   - Verify lookups are using your primary nameserver

# dnsmasqDN

Here, we run dnsmasq as a background service on macOS. The dnsmasq configuration described below implements DNS splitting.

## Setup

1. `brew install dnsmasq`
2. Edit `$(brew --prefix)/etc/dnsmasq.conf` and append the following lines:
    ```
        # Ignore /etc/resolv.conf
        no-resolv

        # Direct IP of BIND container
        server=/loc/{{BIND IP}}
        # OR with host port forwarding
        server=/loc/127.0.0.1

        # Forward all other requests to Google's public DNS server
        server=8.8.8.8

        # Only listen for DNS queries on localhost
        listen-address=127.0.0.1

        # Required due to macOS limitations
        bind-interfaces
    ```
3. `sudo brew services start dnsmasq`

## Point macOS to the new server

### Via GUI

To point to the new split DNS server, follow these steps:

1. Open up "System Preferences," click on "Network."
2. The first interface with a green ball is your default interface. Click on it and then "Advanced."
3. Click on the "DNS" tab.
4. Click on "+" and type in "127.0.0.1"
5. Click on "OK"
6. Click on "Apply"

### Via Scripting

To point to the new DNS server via scripting, run the following command (replacing "Wi-Fi" with whatever your interface name is):

`sudo networksetup -setdnsservers "Wi-Fi" 127.0.0.1`

To clear the DNS server change:

`sudo networksetup -setdnsservers "Wi-Fi" empty`

## Testing

Check that hosts within each of your "server=" directives resolves as expected.

`dig {myproject.loc}`

Check that hosts that do not match any "server=" directive go out to the default DNS server.

`dig www.google.com`

## Troubleshooting

### Flush DNS caches

If DNS queries are not behaving as expected, flush macOS's DNS cache.

`sudo dscacheutil -flushcache; sudo killall -HUP mDNSResponder`
