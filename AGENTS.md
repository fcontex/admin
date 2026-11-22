# AGENTS.md

本文件是 AI agent（以及新接手的开发者）在本仓库工作的入口，先读完再动手。完整细则见 `docs/` 下三份文档，本文件只给必须遵守的约定与地图。

## 1. 项目是什么

FContex Admin 是纯 PHP、零构建链、模块化的轻量管理后台开发框架，MIT 协议。单语言（PHP）、服务端渲染、MySQL 单数据库，无 Node、Vite、Composer。

## 2. 仓库地图

```
.
├── AGENTS.md          本文件
├── README.md          对外说明
├── LICENSE            MIT
├── bin/
│   └── newmodule.php  模块脚手架（CLI）
├── docs/
│   ├── 01.项目介绍.md
│   ├── 02.架构设计.md
│   └── 03.开发指南.md
└── src/               部署到 Web 根目录的全部内容
    ├── index.php      唯一 Web 入口
    ├── module/        startup + 所有模块
    ├── store/         运行时存储（config / cache / upload）
    └── install/       安装向导
```

## 3. 架构要点（不可违反）

- 内核只有 system、member 两个不可卸载模块；其余一切功能都是可装卸模块，模块是唯一扩展机制（没有插件、没有第二套体系）。
- 模块两个入口：`index.php` 页面入口只渲染；`control/index.php` 动作接口只读写，动作第一件事必须是 `checkRights`。
- 依赖单向：内核不依赖业务模块；模块之间只能后来者依赖先有者。
- 菜单两层：分组（组不设权限）+ 叶子（引用权限）；单组模块视觉等同旧扁平菜单。

## 4. 命名约定（AI 生成代码的锚点）

| 对象 | 约定 | 示例（模块 demo） |
|---|---|---|
| 模块目录 | 小写单词 | `demo` |
| 声明类 | `FCModule` + `ucfirst(目录)` | `FCModuleDemo` |
| 权限键 | `模块.实体.动词` | `demo.thing.insert` |
| 动作 mode | `fc.实体.动词` | `fc.thing.insert` |
| 类库文件 | `cls.<名称>.php` | `cls.demo.php` |
| 数据表 | 建表脚本写 `fc_`，安装时替换为实际前缀 | `fc_demo` |

## 5. 数据库规则

- 读只有一条路：`DB::table('裸表名')` 查询构造器，出口 `row / rows / value / column / count / exists`；禁止手写 select、禁止 `$D->run()`。
- 写走全局 `$D`：`insert / update / delete / exec`；where 含用户输入必须用数组或 `?` 绑定；字符串 where 只允许已校验的 `in (...)` 列表和 `1=1`。
- 事务用 `$D->transaction(function($D){ ... })`，禁止手写 `beginTransaction / commit / rollback`。
- `raw()` 只接常量表达式，不接任何用户输入。
- 禁止残留：`->query(`、`->fetch(`、`->fetchAll(`、`->insertid(`、`->escape(`、`strSQL(`、`DB_TYPE`。

## 6. 配置归属（改错文件是最常见错误）

| 文件 | 性质 | 能否手改 |
|---|---|---|
| `system.database.php` | 安装生成 | 不入库，仓库仅提供 sample |
| `system.settings.php` | 手工配置 | 可以，上传、Cookie、`skin_default` 等 |
| `system.site.php` | 自动化 | 不要手改，后台保存动作写入 |
| `system.modules.php` | 自动化 | 禁止手改，模块管理安装/更新时生成 |
| 模块 `*.settings.php` | 自动化 | 由模块动作写回 |

## 7. 代码风格

- 缩进用 **Tab**；数组用 `array()`；注释用中文；方法小驼峰；文件 UTF-8 无 BOM、LF 行尾。
- 文件头保留作者与 `www.fcontex.com` 注释块。
- 输出转义：文本 `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')`，数字 `intval()`。
- 数据库字段用表前缀缩写（如 `dv_id`）。

## 8. 验证步骤（每次改动后）

```bash
# 语法检查（Windows 可用 phpstudy 自带的 php）
find src -name "*.php" -exec php -l {} \;

# 残留扫描
grep -rn "\->query(\|\->fetch(\|\->insertid(\|strSQL(\|DB_TYPE" src/module
```

## 9. 新增模块：用脚手架

```bash
php bin/newmodule.php <模块名> [显示名]
# 例：php bin/newmodule.php device 设备管理
```

脚手架生成 `module.php`、`index.php`、`control/index.php`、`template`、`install.mysql.sql` 全套骨架；随后在后台「模块管理」点安装，菜单与图标自动上桌面。

## 10. 深入阅读

- `docs/01.项目介绍.md`：定位、亮点、安全、路线
- `docs/02.架构设计.md`：请求生命周期、模块系统、皮肤与附件机制
- `docs/03.开发指南.md`：模块开发五步、数据库 API 总表、常见配方、禁忌清单
