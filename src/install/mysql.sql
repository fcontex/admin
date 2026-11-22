/*
FContex Admin 核心数据表（安装向导执行）
仅包含内核表：fc_attached / fc_group / fc_logs / fc_user
各模块数据表由模块install回调创建（见 module/*/install.mysql.sql）
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `fc_attached`
-- ----------------------------
DROP TABLE IF EXISTS `fc_attached`;
CREATE TABLE `fc_attached` (
  `at_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `at_token` char(32) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `at_uid` int(10) unsigned DEFAULT '0',
  `at_hits` int(10) unsigned DEFAULT '0',
  `at_filename` varchar(100) DEFAULT NULL,
  `at_filenewname` varchar(100) DEFAULT NULL,
  `at_title` varchar(100) DEFAULT NULL,
  `at_isimage` tinyint(1) unsigned DEFAULT '0',
  `at_size` int(10) unsigned DEFAULT '0',
  `at_suffix` varchar(10) DEFAULT NULL,
  `at_filetype` varchar(100) DEFAULT NULL,
  `at_dir` varchar(100) DEFAULT NULL,
  `at_remote` int(10) DEFAULT '0',
  `at_url` varchar(200) DEFAULT NULL,
  `at_time` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`at_id`),
  UNIQUE KEY `at_token` (`at_token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------
-- Records of fc_attached
-- ----------------------------
INSERT INTO `fc_attached` VALUES ('1', '1', '0', '0', 'root.png', '735951354083439.png', '', '1', '9791', 'png', 'application/octet-stream', 'member/201211/28/', '0', '', '1354083439');

-- ----------------------------
-- Table structure for `fc_group`
-- ----------------------------
DROP TABLE IF EXISTS `fc_group`;
CREATE TABLE `fc_group` (
  `gr_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `gr_name` varchar(100) DEFAULT NULL,
  `gr_desc` varchar(200) DEFAULT NULL,
  `gr_rights` varchar(10000) DEFAULT NULL,
  `gr_time` int(10) unsigned DEFAULT '0',
  PRIMARY KEY (`gr_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------
-- Records of fc_group
-- ----------------------------
INSERT INTO `fc_group` VALUES ('1', '系统管理员', '系统管理员权限不受限制。', 'system.login,system.site.select,system.site.update,system.modules.select,system.modules.insert,system.modules.update,system.modules.disable,system.modules.order,system.modules.delete,system.logs.select,system.logs.delete,system.file.select,system.file.update,system.file.delete,system.file.upload,system.cache,user.group.select,user.group.insert,user.group.update,user.group.delete,user.select,user.insert,user.update,user.delete,content.settings,content.category.select,content.category.insert,content.category.update,content.category.order,content.category.delete,content.select,content.insert,content.update,content.delete,content.page.select,content.page.insert,content.page.update,content.page.delete,content.tags.select,content.tags.update,content.tags.delete,content.navigate.select,content.navigate.insert,content.navigate.update,content.navigate.delete,gbook.select,gbook.update,gbook.delete,comment.select,comment.update,comment.delete,message.settings', '1340031851');
INSERT INTO `fc_group` VALUES ('2', '普通管理员', '普通管理员权限受限制。', 'system.login,user.group.select,user.select,content.category.select,content.insert,content.update,content.delete,content.tags.select,content.tags.update,content.tags.delete,gbook.select,gbook.update,gbook.delete,comment.select,comment.update,comment.delete', '1340464421');

-- ----------------------------
-- Table structure for `fc_logs`
-- ----------------------------
DROP TABLE IF EXISTS `fc_logs`;
CREATE TABLE `fc_logs` (
  `lg_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `lg_event` varchar(100) DEFAULT NULL,
  `lg_uid` int(10) unsigned DEFAULT '0',
  `lg_ip` varchar(100) DEFAULT NULL,
  `lg_iparea` varchar(100) DEFAULT NULL,
  `lg_time` int(10) unsigned DEFAULT '0',
  PRIMARY KEY (`lg_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------
-- Records of fc_logs
-- ----------------------------

-- ----------------------------
-- Table structure for `fc_user`
-- ----------------------------
DROP TABLE IF EXISTS `fc_user`;
CREATE TABLE `fc_user` (
  `us_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `us_group` int(10) unsigned DEFAULT '0',
  `us_username` varchar(100) DEFAULT NULL,
  `us_password` varchar(255) DEFAULT NULL,
  `us_name` varchar(100) DEFAULT NULL,
  `us_desc` varchar(200) DEFAULT NULL,
  `us_email` varchar(100) DEFAULT NULL,
  `us_phone` varchar(100) DEFAULT NULL,
  `us_address` varchar(300) DEFAULT NULL,
  `us_skin` varchar(100) DEFAULT NULL,
  `us_face` varchar(100) DEFAULT NULL,
  `us_time` int(10) unsigned DEFAULT '0',
  `us_resetcodetime` int(10) unsigned DEFAULT '0',
  `us_token` varchar(64) DEFAULT '',
  `us_tokenexpire` int(10) unsigned DEFAULT '0',
  PRIMARY KEY (`us_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------
-- Records of fc_user
-- ----------------------------
INSERT INTO `fc_user` VALUES ('1', '1', 'admin', '$2y$10$SbN5qfhL5yDbIFJQJDGXyOuX3jTDWxwW96tx2hL/9o.HZcj/yNRZu', 'admin', '', '', '', NULL, 'default', 'member/201211/28/735951354083439.png', '1341129209', '0', '', '0');
