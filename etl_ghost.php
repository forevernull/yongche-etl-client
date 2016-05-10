<?php
$conf = json_decode(file_get_contents(__DIR__."/conf.json"), true);
$conf_db = $conf['db'];

$db = new mysqli($conf_db['host'], $conf_db['username'], $conf_db['password'], $conf_db['database']);
$sql = "select * from etl_jobs_run where status = 0";
$stmt = $db->query($sql);
if($stmt){
    $jobs = $stmt->fetch_all(MYSQLI_ASSOC);
    foreach($jobs as $key=>$val){
      $sql = "update etl_jobs_run set status = 2 where id = ".$val['id']." and host = '". $val['host']."'";   
      $db->query($sql);

      $r = proc_open($val['script'], 
          array(
            array("pipe","r"),
            array("pipe","w"),
            array("pipe","w")
          ),
        $pipes
        );
        $result = '';
        $error = '';
        while(!feof($pipes[1])){
            $result .= addslashes(trim(fread($pipes[1], 2096)));
        }
        while(!feof($pipes[2])){
            $error .= addslashes(trim(fread($pipes[2], 2096)));
        }

      $sql = "update etl_jobs_run set status = 3 , stdout = '$result', stderr = '$error' where id= ".$val['id'];
      print_r($sql);
      $db->query($sql);
    }
}
