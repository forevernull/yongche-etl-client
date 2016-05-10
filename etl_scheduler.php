<?php
/**
 * 并行调度器
 * 负责调度脚本, 将需要并行执行的脚本ID放入待执行序列。
 *
 * 目前调度脚本仅根据脚本的执行级别来进行分发 
 */
//获取脚本的ID，以备etl_run脚本并行发出任务

$type = isset($argv[1]) ? $argv[1] : 0;//0 表示按天, 1 表示按小时
$level = isset($argv[2]) ? $argv[2] : 0; //等级
launch_task($type, $level);

function launch_task($type, $level = 0){
    $conf = json_decode(file_get_contents(__DIR__."/conf.json"), true);
    $conf_db = $conf['db'];
    $db = new mysqli($conf_db['host'], $conf_db['username'], $conf_db['password'], $conf_db['database']);
    $sql = 'select id from etl_jobs where status = 1 and host = "'. $conf['host'].'" and level = '.$level." and type = $type";
    #print_r($sql);
    $script = $db->query($sql)->fetch_all(MYSQLI_ASSOC);
    $ids = "";
    foreach($script as $key => $val){
       $ids .= $val['id']." "; 
    }
    file_put_contents("/tmp/etl".$type.$level, $ids);
}
