# PHP Wifi Auto Reconnect
Simple WiFi connection manager

## Features
 - Auto reconnect on connection loss
 - Auto switch AP (depends on signal strength)
 - Ethernet/WiFi simultaneous mode (multiple default gateways)

## Requirements
 - OS *nix
 - PHP 8+
 - wireless-tools, wpasupplicant, dhclient, iproute2, ping

## Installation

```bash
sudo apt update
sudo apt install -y wireless-tools wpasupplicant isc-dhcp-common iproute2 psmisc iputils-ping
wget wget https://github.com/azurre/php-wifi-auto-reconnect/releases/latest/download/wlan-mgr.phar
# optional, replace `php wlan-mgr.phar` to `wlan-mgr`
chmod +x wlan-mgr.phar && sudo move wlan-mgr.phar /usr/bin/wlan-mgr
```

## Usage

### Run wlan manager
```bash
sudo php wlan-mgr.phar
```

### Help
```bash
 php wlan-mgr.phar -h
```

```
WiFi Connection Manager

Usage:
php command [options]

Options:
-c          Path to JSON config
-h          Show help information
--dry-run   Check all the requirements
Example:
php wlan-mgr -c=/etc/wpa_supplicant/config.json
```

### Dry-run
```bash
 sudo php wlan-mgr.phar --dry-run
```

```
[+] iwconfig (wireless-tools)
[+] iwlist (wireless-tools)
[+] Wlan interface (wlan0)
[+] WPA Supplicant (wpasupplicant)
[+] DHCP Client (isc-dhcp-common)
[+] IP (iproute2)
[+] KillAll (psmisc)
[+] Ping (iputils-ping)
```
