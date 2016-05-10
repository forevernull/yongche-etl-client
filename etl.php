<?php
/**
 * ETL 核心脚本
 *
 * 负责脚本依赖检查，执行和执行情况监控
 */
function __autoload($clazz) {
    $file = str_replace('_', '/', $clazz);
    require "/usr/share/pear/$file.php";
}
date_default_timezone_set("Asia/Shanghai");

$conf = json_decode(file_get_contents(__DIR__."/conf.json"), true);
$conf_db = $conf['db'];
$job_id = $argv[1];

//I. 准备
$db = new mysqli($conf_db['host'], $conf_db['username'], $conf_db['password'], $conf_db['database']);
$sql = 'select * from etl_jobs where status =1 and host = "'. $conf['host'].'" and id = '.$job_id;
$script = $db->query($sql);
if($script){
    $job = $script->fetch_assoc();
    if(count($job)){ 
        //执行检查逻辑，直到所有依赖脚本可用
        if($job['pre_script']) dependent_check($job['pre_script']);

        //II. 执行
        $start_time = time();
        $cmd = proc_open(htmlspecialchars_decode($job['script'], ENT_QUOTES), 
            array(
                array("pipe","r"),
                array("pipe","w"),
                array("pipe","w")
            ),
            $pipes
        );
        $stdout = '';
        $stderr = '';
        while(!feof($pipes[1])){
            $stdout .= addslashes(trim(fread($pipes[1], 2096)));
        }
        while(!feof($pipes[2])){
            $stderr .= addslashes(trim(fread($pipes[2], 2096)));
        }

        if($stderr){
            sendSMS($job['user_telphone'], substr("#ERROR#".$job['id']." ".$job['script']." ".$stderr, 0, 70));
        }

        //III. 记录日志
        $txt_log = date("Y-m-d H:i:s")."#".htmlspecialchars_decode($job['script'], ENT_QUOTES)."\n";
        file_put_contents("/tmp/etl_".date("Y-m-d").".log",  $txt_log, FILE_APPEND); //输出文件日志
        
        $log = array(
            "stat_time" => strtotime(date("Y-m-d")),
            "job_id" => $job_id,
            "script" => $job['script'],
            "start_time" => $start_time,
            "end_time" => time(),
            "stdout" => $stdout,
            "stderr" => $stderr,
        );

        $sql = "insert into etl_jobs_log values ('".implode("','", array_values($log))."')";
        $db->query($sql);
    }else{
        echo "脚本: $job_id 未启用或者未找到！";
    }
}else{
    echo "脚本：$job_id 未找到！";
}

function dependent_check($pre_script){
    //@TODO: 目前只提供按天的脚本依赖检测, 按小时的脚本依赖原则是不提供依赖关系检测
    global $db, $job_id;
    $startTime = strtotime(date("Y-m-d"));
    $endTime = $startTime + 86400;

    $pre_scripts = explode(',', $pre_script);
    while($pre_scripts){
        foreach($pre_scripts as $key=>$val){
            $sql = "
                select t1.must_lock, ifnull(t2.cnt, 0) cnt
                from (
                    select id, must_lock 
                    from etl_jobs 
                    where id = $val
                ) t1 
                left join (
                    select jobs_id, count(*) cnt
                    from etl_jobs_log
                    where stat_time >= $startTime and stat_time < $endTime and jobs_id =  $val
                ) t2 on t1.id = t2.jobs_id
            "; 
            #print_r($sql);
            $result = $db->query($sql)->fetch_assoc();
            if($result['must_lock'] && $result['cnt'] <= 0){
                echo "[".$job_id."] waiting $val \n";
                sleep(30);
            }else{
                unset($pre_scripts[$key]);
            }
        }//foreach
    }//while
}

function sendSMS($cellphone, $info){
    $data = array(
            'CELLPHONE' => $cellphone,
            'YANZHENGMA' => $info,
            "__NO_ASSEMBLE"=>"1",
            'FLAG' => 47
            );

    YCL_Atm2::sendEvent(17,$data);
}
