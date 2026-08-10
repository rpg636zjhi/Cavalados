<div align="center">
  <h1>Cavalados API v2.0</h1>
  <p>
    Brazilian multi-version custom server software for Minecraft Pocket Edition 0.14.x and 0.15.10, focused on performance, stability, and dynamic configuration.
  </p>
  <p>
    <a href="README.md"><strong>Português</strong></a> |
    <strong>English</strong> |
    <a href="README_ZH.md"><strong>简体中文</strong></a>
  </p>
  <p>
    <a href="https://github.com/gstvmonteiro/Cavalados-AnyVersion-PMMP2/issues">Report a bug</a> |
    <a href="https://github.com/gstvmonteiro/Cavalados-AnyVersion-PMMP2/issues">Request a feature</a>
  </p>
  <p>
    <img src="https://img.shields.io/badge/version-2.0-6f42c1" alt="Version 2.0">
    <img src="https://img.shields.io/github/license/gstvmonteiro/Cavalados-AnyVersion-PMMP2" alt="License">
    <img src="https://img.shields.io/github/stars/gstvmonteiro/Cavalados-AnyVersion-PMMP2?style=social" alt="Stars">
    <img src="https://img.shields.io/github/forks/gstvmonteiro/Cavalados-AnyVersion-PMMP2?style=social" alt="Forks">
    <img src="https://img.shields.io/github/last-commit/gstvmonteiro/Cavalados-AnyVersion-PMMP2" alt="Last commit">
  </p>
</div>

---

## About version 2.0

**Cavalados API v2.0** marks a new stage of the project, featuring changes made by **Madson_1000 Mcpe** to make the server lighter, more stable, and ready for larger communities.

This version includes major performance improvements, reduced lag, DDoS protection, and dynamic JSON-based configuration.

## Main improvements

| Feature | What changed in v2.0 |
| --- | --- |
| **Performance** | Optimizations provide a smoother experience with less lag. |
| **Capacity** | Designed to support more than 80 concurrent players. |
| **Anti-DDoS protection** | Integrated protection with packet control to reinforce server security. |
| **Dynamic configuration** | Supported features can be enabled or disabled through JSON without restarting the server. |
| **Multi-version** | Compatible with MCPE 0.14.x and 0.15.10. |

> The supported player count may vary depending on hardware, network connection, maps, plugins, and server configuration.

## Configuration without restarts

In v2.0, supported options can be changed directly in the JSON files. The server applies these changes while running, allowing features to be enabled or disabled without a restart.

This makes quick adjustments easier and reduces downtime during server administration.

## Features

- Support for MCPE 0.14.x and 0.15.10.
- Improved stability on servers with many players.
- Capacity for more than 80 players, depending on the environment.
- Integrated Anti-DDoS protection.
- Packet control.
- Dynamic JSON configuration.
- Compatible with hosting services and local servers.
- Open source under the GNU GPL v3 license.

## Technologies used

- **PHP 7.x+**
- **PocketMine-MP (Fork)**
- **JSON** for dynamic configuration
- **AntiDdos FZ-MG**

## How to use

Clone this repository or download the latest version and use it as the base for your custom server:

```bash
git clone https://github.com/gstvmonteiro/Cavalados.git
```

Review the configuration files before starting the server in production.

## License

Distributed under the **GNU General Public License v3.0**. See the [LICENSE](LICENSE) file for details.

## Credits

- **Madson_1000 Mcpe** — development of the new v2.0 changes and improvements.
- **m4theuswtfkkj** — fix for a crash related to skin loading.
- **Sunch233** — Simplified Chinese README translation.
- **[@gstvmonteiro](https://github.com/gstvmonteiro)** — repository maintenance and publication of this version.

---

**Repository:** [github.com/gstvmonteiro/Cavalados-AnyVersion-PMMP2](https://github.com/gstvmonteiro/Cavalados-AnyVersion-PMMP2)
