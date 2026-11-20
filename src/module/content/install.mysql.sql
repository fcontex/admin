-- ----------------------------
-- 内容模块数据表（安装回调执行）
-- 建表语句均为 IF NOT EXISTS：已存在的表自动跳过，卸载保留数据，重装不丢数据
-- ----------------------------

-- ----------------------------
-- Table structure for `fc_category`
-- ----------------------------
CREATE TABLE IF NOT EXISTS `fc_category` (
  `cg_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `cg_pid` int(10) unsigned DEFAULT '0',
  `cg_order` int(10) unsigned DEFAULT '0',
  `cg_type` tinyint(1) unsigned DEFAULT '0',
  `cg_url` varchar(200) DEFAULT NULL,
  `cg_target` varchar(10) DEFAULT '_self',
  `cg_title` varchar(100) DEFAULT NULL,
  `cg_desc` varchar(200) DEFAULT NULL,
  `cg_show` int(10) DEFAULT '0',
  `cg_count` int(10) DEFAULT '0',
  `cg_time` int(10) unsigned DEFAULT '0',
  PRIMARY KEY (`cg_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------
-- Table structure for `fc_content`
-- ----------------------------
CREATE TABLE IF NOT EXISTS `fc_content` (
  `ct_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ct_type` tinyint(1) unsigned DEFAULT '0',
  `ct_cid` int(10) unsigned DEFAULT '0',
  `ct_ctitle` varchar(200) DEFAULT NULL,
  `ct_uid` int(10) unsigned DEFAULT '0',
  `ct_username` varchar(200) DEFAULT NULL,
  `ct_title` varchar(200) DEFAULT NULL,
  `ct_title2` varchar(200) DEFAULT NULL,
  `ct_title3` varchar(200) DEFAULT NULL,
  `ct_hits` int(10) unsigned DEFAULT '0',
  `ct_talks` int(10) unsigned DEFAULT '0',
  `ct_tags` varchar(200) DEFAULT NULL,
  `ct_cover` varchar(200) DEFAULT NULL,
  `ct_pictures` text,
  `ct_summary` text,
  `ct_content` text,
  `ct_check` tinyint(1) unsigned DEFAULT '0',
  `ct_fixed` tinyint(1) unsigned DEFAULT '0',
  `ct_quiet` tinyint(1) unsigned DEFAULT '0',
  `ct_inserttime` int(10) unsigned DEFAULT '0',
  `ct_updatetime` int(10) unsigned DEFAULT '0',
  `ct_seo` tinyint(1) unsigned DEFAULT '0',
  `ct_pagetitle` varchar(200) DEFAULT NULL,
  `ct_keywords` varchar(200) DEFAULT NULL,
  `ct_description` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`ct_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------
-- Table structure for `fc_tags`
-- ----------------------------
CREATE TABLE IF NOT EXISTS `fc_tags` (
  `tg_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `tg_title` varchar(100) DEFAULT NULL,
  `tg_color` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`tg_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------
-- Table structure for `fc_navigate`
-- ----------------------------
CREATE TABLE IF NOT EXISTS `fc_navigate` (
  `nv_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nv_title` varchar(100) DEFAULT NULL,
  `nv_url` varchar(200) DEFAULT NULL,
  `nv_target` varchar(10) DEFAULT NULL,
  `nv_order` int(10) unsigned DEFAULT '0',
  `nv_check` tinyint(1) unsigned DEFAULT '0',
  PRIMARY KEY (`nv_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
