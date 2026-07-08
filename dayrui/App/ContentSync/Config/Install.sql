
DROP TABLE IF EXISTS `{dbprefix}content_sync_site`;
CREATE TABLE IF NOT EXISTS `{dbprefix}content_sync_site` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `name` varchar(100) NOT NULL COMMENT '网站名称',
  `api_url` varchar(255) NOT NULL COMMENT '接口地址',
  `api_key` varchar(255) NOT NULL COMMENT '接口密钥',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '启用状态',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='内容同步目标站点配置表';

DROP TABLE IF EXISTS `{dbprefix}content_sync_log`;
CREATE TABLE IF NOT EXISTS `{dbprefix}content_sync_log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `content_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '内容ID',
  `module` varchar(50) NOT NULL DEFAULT '' COMMENT '模块目录',
  `title` varchar(255) NOT NULL DEFAULT '' COMMENT '内容标题',
  `target_site` varchar(100) NOT NULL DEFAULT '' COMMENT '目标站点',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '同步状态',
  `request_data` mediumtext NOT NULL COMMENT '请求数据',
  `response_data` mediumtext NOT NULL COMMENT '响应数据',
  `error_message` text NOT NULL COMMENT '错误信息',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `content_id` (`content_id`),
  KEY `module` (`module`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='内容同步发送日志表';
