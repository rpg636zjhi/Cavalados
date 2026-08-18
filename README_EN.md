<div align="center">
  <h1>Cavalados — codename Auriverde</h1>
  <p>
    Brazilian multi-version custom server software for Minecraft Pocket Edition 0.14.x and 0.15.10, focused on performance, stability, and dynamic configuration.
  </p>
  <p>
    <a href="README.md"><strong>Português</strong></a> |
    <strong>English</strong> |
    <a href="README_ZH.md"><strong>简体中文</strong></a>
  </p>
  <p>
    <a href="https://github.com/gstvmonteiro/Cavalados/issues">Report a bug</a> |
    <a href="https://github.com/gstvmonteiro/Cavalados/wiki/">Documentation</a>
  </p>
  <p>
    <img src="https://img.shields.io/badge/version-2.0-009c3b" alt="Version 2.0">
    <img src="https://img.shields.io/github/license/gstvmonteiro/Cavalados" alt="License">
    <img src="https://img.shields.io/github/stars/gstvmonteiro/Cavalados?style=social" alt="Stars">
    <img src="https://img.shields.io/github/forks/gstvmonteiro/Cavalados?style=social" alt="Forks">
    <img src="https://img.shields.io/github/last-commit/gstvmonteiro/Cavalados" alt="Last commit">
  </p>
</div>

---

## About version 2.0

**Cavalados API v2.0** marks a new stage of the project, featuring changes made by **Madson_1000 Mcpe** to make the server lighter, more stable, and ready for larger communities.

**Auriverde** is the identity of this Brazilian core: a green-and-yellow terminal, `cavalados.yml` configuration, and messages designed for classic MCPE clients.

This version includes major performance improvements, reduced lag, DDoS protection, and advanced configuration in `cavalados.yml`.

## Main improvements

| Feature | What changed in v2.0 |
| --- | --- |
| **Performance** | Optimizations provide a smoother experience with less lag. |
| **Anticheat** | Movement validation, finite-coordinate checks, and rate limits for chat, commands, logins, and connections per IP. |
| **Safe names** | Usernames are limited to ASCII letters, digits, and underscores to prevent corrupted names on legacy clients. |
| **Memory** | The chunk budget is respected, chunk caching is disabled by default, and PHP caches are cleaned periodically. |
| **Capacity** | Designed to support more than 80 concurrent players. |
| **Anti-DDoS protection** | Integrated protection with packet control to reinforce server security. |
| **Advanced configuration** | Supported features are organized in the YAML file `cavalados.yml`. |
| **Multi-version** | Compatible with MCPE 0.14.x and 0.15.10. |

> The supported player count may vary depending on hardware, network connection, maps, plugins, and server configuration.

## Advanced configuration

In v2.0, supported options are centralized in `cavalados.yml` and can be reloaded by an administrator.

This makes quick adjustments easier and reduces downtime during server administration.

## Features

- Support for MCPE 0.14.x and 0.15.10.
- Improved stability on servers with many players.
- Capacity for more than 80 players, depending on the environment.
- Integrated Anti-DDoS protection.
- Packet control.
- Advanced configuration in `cavalados.yml`.
- The acute-accent `u` characters and emojis are blocked in legacy chat.
- Compatible with hosting services and local servers.
- Open source under the GNU GPL v3 license.

## Technologies used

- **PHP 7.x+**
- **PocketMine-MP (Fork)**
- **YAML** for advanced configuration
- **AntiDdos FZ-MG**

## How to use

Clone this repository or download the latest version and use it as the base for your custom server:

```bash
git clone https://github.com/gstvmonteiro/Cavalados.git
```

On Windows x64, provide a trusted PHP 7 package with `pthreads` and pass the archive and its SHA-256 checksum to the installer:

```powershell
Set-ExecutionPolicy -Scope Process Bypass
.\install.ps1 -Archive C:\path\php7.zip -Sha256 SHA256_HASH
.\start.cmd
```

The `php8` branch provides the recommended automatic installer for Windows and Linux.

## Build this server as a Phar file

**On Linux or MacOS**
```bash
./bin/php7/bin/php -dphar.readonly=0 test/ci.php
```
**On Windows (PowerShell)**
```bash
./bin/php/php.exe -d phar.readonly=0 ./test/ci.php
```

Review the configuration files before starting the server in production.

## License

Distributed under the **GNU General Public License v3.0**. See the [LICENSE](LICENSE) file for details.

## Credits

- **Madson_1000 Mcpe** — development of the new v2.0 changes and improvements.
- **m4theuswtfkkj** — fix for a crash related to skin loading.
- **Sunch233 and QwQ12222** — Simplified Chinese README translation.
- **[@gstvmonteiro](https://github.com/gstvmonteiro)** — repository maintenance and publication of this version.

---

**Repository:** [github.com/gstvmonteiro/Cavalados-AnyVersion-PMMP2](https://github.com/gstvmonteiro/Cavalados-AnyVersion-PMMP2)
