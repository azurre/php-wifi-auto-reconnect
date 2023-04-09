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
```

## Usage

### Run wlan manager
```bash
wlan-mgr
```

### Help
```bash
 wlan-mgr -h
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
 sudo wlan-mgr --dry-run
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