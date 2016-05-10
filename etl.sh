#!/bin/bash
# 并行分发器
# 负责触发ETL调度器，并进行并行分发
path=`pwd`
php=`which php`
$php $path"/etl_scheduler.php" 0 0

for i in `cat /tmp/etl00`;
do
{
    cmd="$php $path/etl.php $i"
    echo $cmd
    `$cmd`
    sleep 3
}&
done
wait
echo "LEVEL 0 OVER"

$php $path"/etl_scheduler.php" 0 1
for i in `cat /tmp/etl01`;
do
{
    cmd="$php $path/etl.php $i"
    echo $cmd
    sleep 3
}&
done
wait
echo "LEVEL 1 OVER"
