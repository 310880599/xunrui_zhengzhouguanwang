
DROP TABLE IF EXISTS `{dbprefix}api_auth`;
CREATE TABLE IF NOT EXISTS `{dbprefix}api_auth` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'Id',
  `name` varchar(100) NOT NULL COMMENT '名称',
  `secret` varchar(100) NOT NULL COMMENT '密钥',
  `disabled` tinyint(1) unsigned NOT NULL COMMENT '禁用',
  `setting` text NOT NULL COMMENT '存储数据',
  `inputtime` int(10) unsigned NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `disabled` (`disabled`),
  KEY `inputtime` (`inputtime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='api接口认证表';

DROP TABLE IF EXISTS `{dbprefix}api_http`;
CREATE TABLE IF NOT EXISTS `{dbprefix}api_http` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'Id',
  `name` varchar(100) NOT NULL COMMENT '名称',
  `content` text NOT NULL COMMENT '数据格式',
  `disabled` tinyint(1) unsigned NOT NULL COMMENT '禁用',
  `inputtime` int(10) unsigned NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `disabled` (`disabled`),
  KEY `inputtime` (`inputtime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='api接口http数据表';

DROP TABLE IF EXISTS `{dbprefix}api_config`;
CREATE TABLE IF NOT EXISTS `{dbprefix}api_config` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'Id',
  `name` varchar(50) NOT NULL,
  `value` mediumtext NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='api属性配置表';