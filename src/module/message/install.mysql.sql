-- ----------------------------
-- 消息模块数据表（安装回调执行）
-- 建表语句均为 IF NOT EXISTS：已存在的表自动跳过，卸载保留数据，重装不丢数据
-- ----------------------------

-- ----------------------------
-- Table structure for `fc_comment`
-- ----------------------------
CREATE TABLE IF NOT EXISTS `fc_comment` (
  `cm_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `cm_cid` int(10) unsigned DEFAULT '0',
  `cm_ctitle` varchar(200) DEFAULT NULL,
  `cm_toid` int(10) unsigned DEFAULT '0',
  `cm_topid` int(10) unsigned DEFAULT '0',
  `cm_uid` int(10) unsigned DEFAULT '0',
  `cm_name` varchar(100) DEFAULT NULL,
  `cm_toname` varchar(100) DEFAULT NULL,
  `cm_email` varchar(100) DEFAULT NULL,
  `cm_url` varchar(200) DEFAULT NULL,
  `cm_content` varchar(10000) DEFAULT NULL,
  `cm_ip` varchar(100) DEFAULT NULL,
  `cm_iparea` varchar(100) DEFAULT NULL,
  `cm_check` tinyint(1) unsigned DEFAULT '0',
  `cm_time` int(10) unsigned DEFAULT '0',
  `cm_update` int(10) unsigned DEFAULT '0',
  `cm_postid` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`cm_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------
-- Table structure for `fc_gbook`
-- ----------------------------
CREATE TABLE IF NOT EXISTS `fc_gbook` (
  `gb_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `gb_toid` int(10) unsigned DEFAULT '0',
  `gb_topid` int(10) unsigned DEFAULT '0',
  `gb_uid` int(10) unsigned DEFAULT '0',
  `gb_name` varchar(100) DEFAULT NULL,
  `gb_toname` varchar(100) DEFAULT NULL,
  `gb_email` varchar(100) DEFAULT NULL,
  `gb_url` varchar(200) DEFAULT NULL,
  `gb_content` varchar(10000) DEFAULT NULL,
  `gb_ip` varchar(100) DEFAULT NULL,
  `gb_iparea` varchar(100) DEFAULT NULL,
  `gb_check` tinyint(1) unsigned DEFAULT '0',
  `gb_time` int(10) unsigned DEFAULT '0',
  `gb_update` int(10) unsigned DEFAULT '0',
  `gb_postid` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`gb_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
