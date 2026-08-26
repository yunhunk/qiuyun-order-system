# 秋云自助下单系统 V2版

PHP 自助下单系统完整源码（2025-10-25 版本）。

## 目录结构

| 目录/文件 | 说明 |
|---|---|
| `admin/` | 管理后台（站点、商品、订单、提现等管理） |
| `user/` | 用户中心 |
| `api.php` / `ajax.php` / `ajax.new.php` | 接口入口 |
| `template/` | 前台模板 |
| `includes/` | 核心类库 |
| `install/` | 安装程序 |
| `cron/` / `cron.php` | 定时任务 |
| `assets/` | 静态资源（JS/CSS） |
| `vendor/` | 第三方依赖 |
| `dbconfig.php` | 数据库配置 |

## 部署要点

1. PHP + MySQL 环境
2. 在 `dbconfig.php` 中填写数据库连接信息
3. 通过 `install/` 完成安装

## Git 说明

- 运行时日志（`includes/logs/`，约 328MB）与缓存（`cache/`）已通过 `.gitignore` 排除
- `dbconfig.php` 为空模板，不含敏感凭据
