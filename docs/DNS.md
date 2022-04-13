# dnsmasqDN

Sometimes you may want to use a DNS server for specific domain requests and another DNS server for all other requests. This is helpful, for instance, when connected to a VPN. For hosts behind that VPN you want to use the VPN's DNS server but all other hosts you want to use Google's public DNS. This is called "DNS splitting."

Here, we run dnsmasq as a background service on macOS. The dnsmasq configuration described below implements DNS splitting.

## Install

brew install dnsmasq
Don't have Homebrew? Follow the instructions here: https://brew.sh

## Setup

`nano $(brew --prefix)/etc/dnsmasq.conf`

Add the following lines:
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

## Start dnsmasq

Run the following command to start dnsmasq immediately and have it start on reboot.

sudo brew services start dnsmasq
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

sudo networksetup -setdnsservers "Wi-Fi" 127.0.0.1
To clear the DNS server change:

sudo networksetup -setdnsservers "Wi-Fi" empty
## Test

Check that hosts within each of your "server=" directives resolves as expected.

dig somehost.domain.com
Check that hosts that do not match any "server=" directive go out to the default DNS server.

dig www.google.com
## Troubleshooting

### Flush DNS caches

If DNS queries are not behaving as expected, flush macOS's DNS cache.

`sudo dscacheutil -flushcache; sudo killall -HUP mDNSResponder`
