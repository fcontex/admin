# FContex Admin

> 纯 PHP、无前端构建、模块化、声明式权限的管理后台开发框架——为不想进入 Node 工具链的人，也为 AI agent。

FContex Admin 是一个轻量级管理后台开发框架，用于快速构建各类需要后台管理界面的系统：设备管理、业务后台、内部工具、小型信息系统等。它的前身是 2012 年发布的 fcontex CMS（最后公开发布版本 1.0.4），现转型为面向管理类系统的开发框架。

- 官网：www.fcontex.com
- 协议：MIT（见 [LICENSE](LICENSE)）

## 亮点

- **轻量纯粹**：纯 PHP + MySQL/SQLite，无 Node 工具链、无前端构建、无 Composer 依赖，拷贝即部署
- **模块热拔插**：内核仅 system + member，其余皆为可装卸模块；后台"模块管理"一键启用/停用
- **声明式权限**：模块在 `module.php` 中声明权限，安装时注册；操作级权限控制，简洁完整，无节点扫描
- **WebOS 窗口化后台**：桌面隐喻的后台界面，菜单图标随模块装卸自动增减，装模块如装 App
- **响应式界面**：适配移动端访问，后台管理不受设备限制
- **AI 协作友好**：小体积、单语言、约定优于配置——整个项目可读进一个上下文，agent 可放心修改

## 环境要求

- PHP 5.5 及以上（密码散列使用 `password_hash`），推荐 7.4 / 8.x
- MySQL 5.5.3 及以上（utf8mb4）
- Apache / IIS 等 WEB 服务器（附 .htaccess 与 web.config）

## 快速开始

1. 将 `src` 目录下的所有文件拷贝到 WEB 服务器的站点目录
2. 浏览器访问 `http://你的域名/install`
3. 按安装向导提示完成安装

安装完成后请删除 `install` 目录。站点页面提供控制台登录入口，登录后即进入 WebOS 窗口化后台桌面。

## 目录结构

```
├── LICENSE               MIT 协议
├── README.md             本文件
├── docs/                 项目文档
└── src/                  源代码
    ├── module/
    │   ├── system/       内核模块（不可卸载）
    │   ├── member/       用户与权限模块（不可卸载）
    │   ├── content/      内容模块
    │   ├── message/      留言模块
    │   └── ...           其它业务模块
    └── install/          安装向导
```

## 与同类框架对比

| | FContex Admin | FastAdmin | BuildAdmin |
|------|------|------|------|
| 后端 | 自研内核（纯 PHP） | ThinkPHP 5 | ThinkPHP 8 |
| 前端 | 服务端渲染，无构建 | jQuery + RequireJS | Vue3 + TS + Vite |
| 后台形态 | WebOS 窗口化 | 传统侧栏式 | 传统侧栏式 |
| 权限 | 声明式，操作级 | 节点规则 | 节点规则 |
| 部署 | 拷贝即部署 | 常规 | 需 Node 构建 |

差异化的本质：同类框架在加码能力，FContex Admin 在降低负担。

## 文档

- [01. 项目介绍](docs/01.项目介绍.md)——定位、亮点、架构总览、适用场景与路线
- [02. 架构设计](docs/02.架构设计.md)——请求生命周期、内核组件、模块系统、权限体系

## License

[MIT](LICENSE) Copyright (c) 2012-2026 Alan
