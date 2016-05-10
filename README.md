## Yongche ETL 系统 - 客户端[本项目仅用于内部使用]

### 项目概述
BI数据ETL（Extract, Transform & Load）项目旨在完成BI涉及到的数据从线上生产环境到数据仓库的抽取、转换和加载的任务，并提供准确、高效、便捷、低耗的数据处理能力。

### 客户端详情
- `etl.sh` ETL分发器， 负责脚本分发，客户端入口文件，需要加入到crontab中。
- `etl_hour.sh` 按小时的任务分发器。
- `etl.php` ETL核心文件，负责脚本执行前的检测、脚本执行、脚本结果汇总等工作。
- `etl_scheduler.php` ETL调度程序，负责脚本的调度。
- `conf.json` ETL配置文件, 主要包括宿主机host配置和数据库配置等。

- `etl_ghost.sh` 负责处理主机提交的任务。

### 安装
- 将客户端代码拷贝到宿主机上
- 配置客户端, 修改conf.json文件
```
{
    "host" : "192.168.1.100",//宿主机地址
    "db":{
        "host" : "127.0.0.1",
        "port" : 3306, 
        "username" : "username",
        "password" : "password",
        "database" : "database"
    }
}
```
- 将`etl.sh`加入到crontab中, 执行`crontab -e`加入如下代码:
```
0 1 * * * {path}/etl.sh #每天1点整开始执行脚本
*/1 * * * {path}/etl_ghost.sh #每分钟检测一次
```

### 脚本依赖关系
- _强制依赖_ 阻塞后续依赖脚本直到本脚本运行结束后方可执行。
- _非强制依赖_ 后续脚本执行不会被阻塞。
- _多依赖关系_ 脚本依赖于多个脚本。
- _单依赖关系_ 脚本依赖于单个脚本。

