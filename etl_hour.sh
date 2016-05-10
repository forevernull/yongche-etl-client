#!/bin/bash
# 小时并行分发器
# 负责触发ETL调度器，并进行并行分发
path=`pwd`
php=`which php`

php $path"/etl_scheduler.php" 1 0

for i in `cat /tmp/etl10`;
do
{
    `$php $path/etl.php $i`
    sleep 3
}&
done
