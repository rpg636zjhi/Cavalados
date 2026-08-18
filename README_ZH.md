> ⚠️ **重要声明**：当前版本已经修复老版本的漏洞（玩家通过聊天栏执行代码），请放心使用

---
<div align="center">
  <h1>Cavalados — Auriverde</h1>
  <p>
    面向 Minecraft Pocket Edition 0.14.3 和 0.15.10 的巴西多版本自定义服务器软件，专注于性能、稳定性和动态配置。
  </p>
  <p>
    <a href="README.md"><strong>Português</strong></a> |
    <a href="README_EN.md"><strong>English</strong></a> |
    <strong>简体中文</strong>
  </p>
  <p>
    <a href="https://github.com/gstvmonteiro/Cavalados/issues">报告问题</a>
    <a href="https://github.com/gstvmonteiro/Cavalados/wiki/">文档</a>
  </p>
  <p>
    <img src="https://img.shields.io/badge/%E7%89%88%E6%9C%AC-2.0-009c3b" alt="版本 2.0">
    <img src="https://img.shields.io/github/license/gstvmonteiro/Cavalados" alt="许可证">
    <img src="https://img.shields.io/github/stars/gstvmonteiro/Cavalados?style=social" alt="星标">
    <img src="https://img.shields.io/github/forks/gstvmonteiro/Cavalados?style=social" alt="复刻">
    <img src="https://img.shields.io/github/last-commit/gstvmonteiro/Cavalados" alt="最近提交">
  </p>
</div>

---

## 关于 2.0 版本

**Cavalados API v2.0** 代表项目进入了一个新阶段。本版本包含由 **Madson_1000 Mcpe** 完成的新修改，使服务器运行更轻量、更稳定，并能够服务更大的玩家社区。

**Auriverde** 是这个巴西核心的独立身份：绿色和黄色的终端、`cavalados.yml` 配置，以及为经典 MCPE 客户端设计的消息。

此版本带来了重要的性能优化，减少了卡顿，并加入了 DDoS 防护以及 `cavalados.yml` 高级配置。

## 主要改进

| 功能 | v2.0 的变化 |
| --- | --- |
| **性能** | 通过优化提供更流畅、卡顿更少的游戏体验。 |
| **反作弊** | 验证移动和有限坐标，并限制聊天、命令、登录频率以及每个 IP 的连接数。 |
| **安全名称** | 用户名仅允许 ASCII 字母、数字和下划线，避免旧客户端显示损坏的名称。 |
| **内存** | 遵守区块预算，默认关闭区块缓存，并定期清理 PHP 缓存。 |
| **玩家容量** | 设计目标为支持 80 名以上玩家同时在线。 |
| **Anti-DDoS 防护** | 集成数据包控制，提高服务器的安全性。 |
| **高级配置** | 受支持的功能集中在 YAML 文件 `cavalados.yml` 中。 |
| **多版本支持** | 兼容 MCPE 0.14.3 和 0.15.10。 |

> 实际支持的玩家数量可能会因硬件、网络连接、地图、插件和服务器配置而有所不同。

## 高级配置

在 v2.0 中，受支持的选项集中在 `cavalados.yml` 中，并可由管理员重新加载。

这让快速调整更加方便，并减少服务器管理期间的停机时间。

## 功能特性

- 支持 MCPE 0.14.3 和 0.15.10。
- 提高多人服务器的稳定性。
- 根据运行环境，可支持 80 名以上玩家。
- 集成 Anti-DDoS 防护。
- 数据包控制。
- 通过 `cavalados.yml` 进行高级配置。
- 在旧版聊天中拦截带尖音符的 `u` 字符和表情符号。
- 兼容托管服务和本地服务器。
- 根据 GNU GPL v3 许可证开源发布。

## 使用的技术

- **PHP 7.x+**
- **PocketMine-MP（分支）**
- 用于高级配置的 **YAML**
- **AntiDdos FZ-MG**

## 使用方法

克隆此仓库或下载最新版本，并将其用作自定义服务器的基础：

```bash
git clone https://github.com/gstvmonteiro/Cavalados.git
```

在 Windows x64 上，请提供可信的 PHP 7 `pthreads` 软件包，并把压缩包路径和 SHA-256 传给安装程序：

```powershell
Set-ExecutionPolicy -Scope Process Bypass
.\install.ps1 -Archive C:\path\php7.zip -Sha256 SHA256_HASH
.\start.cmd
```

`php8` 分支提供推荐的 Windows 和 Linux 自动安装程序。

## 将此服务器构建为Phar文件

**On Linux or MacOS**
```bash
./bin/php7/bin/php -dphar.readonly=0 test/ci.php
```
**On Windows (PowerShell)**
```bash
./bin/php/php.exe -d phar.readonly=0 ./test/ci.php
```


在生产环境中启动服务器之前，请先检查配置文件。

## 许可证

本项目根据 **GNU 通用公共许可证 v3.0** 分发。详情请参阅 [LICENSE](LICENSE) 文件。继承自上游项目的文件在适用时保留各自的许可证声明。

## 鸣谢

- **Madson_1000 Mcpe** — v2.0 新修改和改进的开发者。
- **m4theuswtfkkj** — 修复与皮肤加载相关的崩溃问题。
- **Sunch233 和 Samuel** — 简体中文 README 翻译。
- **[@gstvmonteiro](https://github.com/gstvmonteiro)** — 仓库维护与本版本发布。

---

**项目仓库：** [github.com/gstvmonteiro/Cavalados-AnyVersion-PMMP2](https://github.com/gstvmonteiro/Cavalados-AnyVersion-PMMP2)
