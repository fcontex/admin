<?php
/*自动化配置文件*/
return array (
  'system' => 
  array (
    'for' => '1.0.1+',
    'name' => '系统配置',
    'icon' => 'module.png',
    'desc' => '系统核心，提供基础运行库、模块控制、全局配置、栏目管理等最核心的功能。',
    'author' => 'alan',
    'contact' => 'alan@fcontex.com',
    'version' => '1.0',
    'update' => '2012.05',
    'support' => 'www.fcontex.com',
    'menus' => 
    array (
      '系统配置' => 
      array (
        'items' => 
        array (
          '平台配置' => 
          array (
            'url' => 'fc.settings',
            'icon' => 'settings.png',
            'rights' => 'system.site.select',
          ),
          '模块管理' => 
          array (
            'url' => 'fc.modules',
            'icon' => 'modules.png',
            'rights' => 'system.modules.select',
          ),
          '缓存管理' => 
          array (
            'url' => 'fc.cache',
            'icon' => 'cache.png',
            'rights' => 'system.cache',
          ),
          '附件管理' => 
          array (
            'url' => 'fc.files',
            'icon' => 'files.png',
            'rights' => 'system.file.select',
          ),
          '操作日志' => 
          array (
            'url' => 'fc.logs',
            'icon' => 'logs.png',
            'rights' => 'system.logs.select',
          ),
        ),
      ),
    ),
    'rights' => 
    array (
      'system.login' => '控制台登录',
      'system.site.select' => '配置查看',
      'system.site.update' => '配置更新',
      'system.modules.select' => '模块查看',
      'system.modules.insert' => '模块安装',
      'system.modules.update' => '模块更新',
      'system.modules.disable' => '模块禁用',
      'system.modules.order' => '模块排序',
      'system.modules.delete' => '模块卸载',
      'system.logs.select' => '日志查看',
      'system.logs.delete' => '日志清理',
      'system.file.select' => '附件查看',
      'system.file.update' => '附件更新',
      'system.file.delete' => '附件删除',
      'system.file.upload' => '上传文件',
      'system.cache' => '缓存管理',
    ),
  ),
  'member' => 
  array (
    'for' => '1.0.1+',
    'name' => '用户管理',
    'icon' => 'module.png',
    'desc' => '用户模块，提供用户管理和权限控制功能。',
    'author' => 'Alan',
    'contact' => 'alan@fcontex.com',
    'version' => '1.0',
    'update' => '2012.05',
    'support' => 'www.fcontex.com',
    'menus' => 
    array (
      '用户管理' => 
      array (
        'items' => 
        array (
          '分组查看' => 
          array (
            'url' => 'fc.group.select',
            'icon' => 'group.select.png',
            'rights' => 'user.group.select',
          ),
          '分组添加' => 
          array (
            'url' => 'fc.group.insert',
            'icon' => 'group.insert.png',
            'rights' => 'user.group.insert',
          ),
          '用户查看' => 
          array (
            'url' => 'fc.user.select',
            'icon' => 'user.select.png',
            'rights' => 'user.select',
          ),
          '用户添加' => 
          array (
            'url' => 'fc.user.insert',
            'icon' => 'user.insert.png',
            'rights' => 'user.insert',
          ),
        ),
      ),
    ),
    'rights' => 
    array (
      'user.group.select' => '分组查看',
      'user.group.insert' => '分组添加',
      'user.group.update' => '分组编辑',
      'user.group.delete' => '分组删除',
      'user.select' => '用户查看',
      'user.insert' => '用户添加',
      'user.update' => '用户编辑',
      'user.delete' => '用户删除',
    ),
  ),
  'content' => 
  array (
    'for' => '1.0.1+',
    'name' => '信息管理',
    'icon' => 'module.png',
    'desc' => '内容模块，提供文章栏目分类及内容管理功能。',
    'author' => 'alan',
    'contact' => 'alan@fcontex.com',
    'version' => '1.0',
    'update' => '2012.05',
    'support' => 'www.fcontex.com',
    'menus' => 
    array (
      '信息管理' => 
      array (
        'items' => 
        array (
          '参数配置' => 
          array (
            'url' => 'fc.settings',
            'icon' => 'settings.png',
            'rights' => 'content.settings',
          ),
          '查看栏目' => 
          array (
            'url' => 'fc.category.select',
            'icon' => 'category.select.png',
            'rights' => 'content.category.select',
          ),
          '添加栏目' => 
          array (
            'url' => 'fc.category.insert',
            'icon' => 'category.insert.png',
            'rights' => 'content.category.insert',
          ),
          '查看信息' => 
          array (
            'url' => 'fc.content.select',
            'icon' => 'content.select.png',
            'rights' => 'content.select',
          ),
          '添加信息' => 
          array (
            'url' => 'fc.content.insert',
            'icon' => 'content.insert.png',
            'rights' => 'content.insert',
          ),
          '查看页面' => 
          array (
            'url' => 'fc.page.select',
            'icon' => 'page.select.png',
            'rights' => 'content.page.select',
          ),
          '添加页面' => 
          array (
            'url' => 'fc.page.insert',
            'icon' => 'page.insert.png',
            'rights' => 'content.page.insert',
          ),
          '标签管理' => 
          array (
            'url' => 'fc.tags.select',
            'icon' => 'tags.select.png',
            'rights' => 'content.tags.select',
          ),
          '导航管理' => 
          array (
            'url' => 'fc.navigate.select',
            'icon' => 'navigate.select.png',
            'rights' => 'content.navigate.select',
          ),
        ),
      ),
    ),
    'rights' => 
    array (
      'content.settings' => '参数配置',
      'content.category.select' => '栏目查看',
      'content.category.insert' => '栏目添加',
      'content.category.update' => '栏目编辑',
      'content.category.order' => '栏目排序',
      'content.category.delete' => '栏目删除',
      'content.select' => '信息查看',
      'content.insert' => '信息添加',
      'content.update' => '信息编辑',
      'content.delete' => '信息删除',
      'content.page.select' => '页面查看',
      'content.page.insert' => '页面添加',
      'content.page.update' => '页面编辑',
      'content.page.delete' => '页面删除',
      'content.tags.select' => '标签查看',
      'content.tags.update' => '标签编辑',
      'content.tags.delete' => '标签删除',
      'content.navigate.select' => '导航查看',
      'content.navigate.insert' => '导航添加',
      'content.navigate.update' => '导航修改',
      'content.navigate.delete' => '导航删除',
    ),
  ),
  'message' => 
  array (
    'for' => '1.0.1+',
    'name' => '消息管理',
    'icon' => 'module.png',
    'desc' => '消息模块，提供留言、文章评论等功能。',
    'author' => 'alan',
    'contact' => 'alan@fcontex.com',
    'version' => '1.0',
    'update' => '2012.05',
    'support' => 'www.fcontex.com',
    'menus' => 
    array (
      '消息管理' => 
      array (
        'items' => 
        array (
          '参数配置' => 
          array (
            'url' => 'fc.settings',
            'icon' => 'settings.png',
            'rights' => 'message.settings',
          ),
          '留言管理' => 
          array (
            'url' => 'fc.gbook.select',
            'icon' => 'gbook.select.png',
            'rights' => 'gbook.select',
          ),
          '评论管理' => 
          array (
            'url' => 'fc.comment.select',
            'icon' => 'comment.select.png',
            'rights' => 'comment.select',
          ),
        ),
      ),
    ),
    'rights' => 
    array (
      'message.settings' => '参数配置',
      'gbook.select' => '查看留言',
      'gbook.update' => '编辑留言',
      'gbook.delete' => '删除留言',
      'comment.select' => '评论列表',
      'comment.update' => '编辑评论',
      'comment.delete' => '删除评论',
    ),
  ),
);
?>