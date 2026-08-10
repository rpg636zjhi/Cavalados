<div align="center">
  <h1>Cavalados API v2.0</h1>
  <p>
    面向 Minecraft Pocket Edition 0.14.x 和 0.15.10 的巴西多版本自定义服务器软件，专注于性能、稳定性和动态配置。
  </p>
  <p>
    <a href="README.md"><strong>Português</strong></a> |
    <a href="README_EN.md"><strong>English</strong></a> |
    <strong>简体中文</strong>
  </p>
  <p>
    <a href="https://github.com/gstvmonteiro/Cavalados/issues">报告问题</a> |
    <a href="https://github.com/gstvmonteiro/Cavalados/issues">建议新功能</a>
  </p>
  <p>
    <img src="https://img.shields.io/badge/%E7%89%88%E6%9C%AC-2.0-6f42c1" alt="版本 2.0">
    <img src="https://img.shields.io/github/license/gstvmonteiro/Cavalados" alt="许可证">
    <img src="https://img.shields.io/github/stars/gstvmonteiro/Cavalados?style=social" alt="星标">
    <img src="https://img.shields.io/github/forks/gstvmonteiro/Cavalados?style=social" alt="复刻">
    <img src="https://img.shields.io/github/last-commit/gstvmonteiro/Cavalados" alt="最近提交">
  </p>
</div>

---

## 关于 2.0 版本

**Cavalados API v2.0** 代表项目进入了一个新阶段。本版本包含由 **Madson_1000 Mcpe** 完成的新修改，使服务器运行更轻量、更稳定，并能够服务更大的玩家社区。

此版本带来了重要的性能优化，减少了卡顿，并加入了 DDoS 防护和基于 JSON 的动态配置系统。

## 主要改进

| 功能 | v2.0 的变化 |
| --- | --- |
| **性能** | 通过优化提供更流畅、卡顿更少的游戏体验。 |
| **玩家容量** | 设计目标为支持 80 名以上玩家同时在线。 |
| **Anti-DDoS 防护** | 集成数据包控制，提高服务器的安全性。 |
| **动态配置** | 可通过 JSON 启用或禁用受支持的功能，无需重启服务器。 |
| **多版本支持** | 兼容 MCPE 0.14.x 和 0.15.10。 |

> 实际支持的玩家数量可能会因硬件、网络连接、地图、插件和服务器配置而有所不同。

## 无需重启的配置

在 v2.0 中，可以直接在 JSON 文件中修改受支持的选项。服务器会在运行时应用这些更改，因此无需重启即可启用或禁用相应功能。

这让快速调整更加方便，并减少服务器管理期间的停机时间。

## 功能特性

- 支持 MCPE 0.14.x 和 0.15.10。
- 提高多人服务器的稳定性。
- 根据运行环境，可支持 80 名以上玩家。
- 集成 Anti-DDoS 防护。
- 数据包控制。
- 基于 JSON 的动态配置。
- 兼容托管服务和本地服务器。
- 根据 GNU GPL v3 许可证开源发布。

## 使用的技术

- **PHP 7.x+**
- **PocketMine-MP（分支）**
- 用于动态配置的 **JSON**
- **AntiDdos FZ-MG**

## 使用方法

克隆此仓库或下载最新版本，并将其用作自定义服务器的基础：

```bash
git clone https://github.com/gstvmonteiro/Cavalados.git
```

在生产环境中启动服务器之前，请先检查配置文件。

## 许可证

本项目根据 **GNU 通用公共许可证 v3.0** 分发。详情请参阅 [LICENSE](LICENSE) 文件。

## 鸣谢

- **Madson_1000 Mcpe** — v2.0 新修改和改进的开发者。
- **m4theuswtfkkj** — 修复与皮肤加载相关的崩溃问题。
- **Sunch233** — 简体中文 README 翻译。
- **[@gstvmonteiro](https://github.com/gstvmonteiro)** — 仓库维护与本版本发布。

---

**项目仓库：** [github.com/gstvmonteiro/Cavalados-AnyVersion-PMMP2](https://github.com/gstvmonteiro/Cavalados-AnyVersion-PMMP2)
