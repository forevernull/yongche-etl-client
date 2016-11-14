CREATE TABLE `etl_jobs` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `script` varchar(200) NOT NULL,
      `pre_script` varchar(100) NOT NULL DEFAULT '',
      `create_time` datetime DEFAULT NULL,
      `update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      `host` varchar(20) NOT NULL DEFAULT 'localhost',
      `status` int(2) NOT NULL DEFAULT '1',
      `level` int(11) NOT NULL DEFAULT '0',
      `must_lock` int(2) NOT NULL DEFAULT '1' COMMENT '是否锁住依赖脚本，即后续脚本必须等待本脚本执行完毕后方可执行， 1为是，0为否',
      `relative_tables` varchar(200) NOT NULL DEFAULT '' COMMENT '修改的表',
      `user_telphone` varchar(200) NOT NULL DEFAULT '',
      `type` int(11) NOT NULL DEFAULT '0',
      PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8


CREATE TABLE `etl_jobs_run` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `script` varchar(200) NOT NULL,
      `host` varchar(20) NOT NULL DEFAULT 'localhost',
      `status` int(2) NOT NULL DEFAULT '0' COMMENT '0 初始化, 1 正在运行， 2 运行结束',
      `stdout` text,
      `stderr` text,
      PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8

CREATE TABLE `etl_jobs_log` (
      `stat_time` int(11) NOT NULL,
      `jobs_id` int(11) NOT NULL,
      `script` varchar(200) NOT NULL,
      `start_time` int(11) NOT NULL DEFAULT '0',
      `end_time` int(11) NOT NULL DEFAULT '0',
      `stdout` text,
      `stderr` text NOT NULL COMMENT '标准错误输出'
) ENGINE=InnoDB DEFAULT CHARSET=utf8


