<?php
include_once __DIR__."/include.php";

$white_list=[
	"ip"=>json_decode(file_get_contents(__DIR__."/config/ip.json"),1),
	"hostname"=>json_decode(file_get_contents(__DIR__."/config/hostname.json"),1),
];

if(in_array($_SERVER['REMOTE_ADDR'],$white_list['ip']) || in_array(gethostname(),$white_list['hostname'])){
	if(isset($_GET['ip'])){
		$_SERVER['REMOTE_ADDR']=$_GET['ip'];
	}
}
$memcache_key_count="login_try_count";
$memcache_key_time="login_try_time";
$memcache_key_count.=$_SERVER['REMOTE_ADDR'];
$memcache_key_time.=$_SERVER['REMOTE_ADDR'];

$lock_time=30*60;
$time=0;
if($tmp=Mcache::get($memcache_key_time)){
	$time+=($tmp+$lock_time)-(time());
}



$login_try=0;
if($tmp=Mcache::get($memcache_key_count)){
	if($time>0){
		$login_try=$tmp;
	}else{
		Mcache::set($memcache_key_count,0,$lock_time);
		Mcache::set($memcache_key_time,time(),$lock_time);
	}
}

if($login_try<3 && mb_strlen($_GET['access_token'])==32 && preg_match("/^[a-z0-9]+$/",$_GET['access_token'])){
	$data=UserList::compactUser($_GET['access_token']);
	
	$status=true;
	$message="成功取得資料";
	Mcache::set($memcache_key_count,0);
}

if(!$data){
	//錯誤3次要鎖30分鐘
	$status=false;
	Mcache::$con->add($memcache_key_count,0,$lock_time);
	Mcache::$con->increment($memcache_key_count);
	$login_try++;
	$message="嘗試第".$login_try."次";
	
	if($login_try >=3){
		Mcache::set($memcache_key_time,time(),$lock_time);
		$message.="{$time}秒解鎖";
	}	
}
$ip=$_SERVER['REMOTE_ADDR'];

echo json_encode(compact(["status","data","message","ip"]));