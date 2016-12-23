<?php
include_once __DIR__."/include.php";
session_start();

$memcache_key_count="login_try_count";
$memcache_key_time="login_try_time";
$memcache_key_count.=$_SERVER['REMOTE_ADDR'];
$memcache_key_time.=$_SERVER['REMOTE_ADDR'];

$login_try=0;
$time=Mcache::get($memcache_key_time)+30*60-time();
if($tmp=Mcache::get($memcache_key_count)){
	if($time>0){
		$login_try=$tmp;
	}else{
		Mcache::$con->set($memcache_key_count,0);
	}
}

if($login_try<3 && mb_strlen($_GET['access_token'])==32 && preg_match("/^[a-z0-9]+$/",$_GET['access_token'])){
	if($result=Cache::get("access_token.id.".$_GET['access_token'])){
		$result['rid']=[];
		if($tmp=Cache::get("UserRole.id.".$result['id'])){
			$result['rid']=$tmp;
			$result['data']=[];
			$result['role']=[];
			$result['role_user']=[];
			foreach($result['rid'] as $rid){
				if($RoleData=Cache::get("RoleData.id.".$rid)){
					$result['data'][$rid]=$RoleData;
				}
				if($RoleList=Cache::get("RoleList.id.".$rid)){
					$result['role'][$rid]=$RoleList;
				}
				if($RoleUser=Cache::get("RoleUser.id.".$rid)){
					foreach($RoleUser as $key=>$uid){
						if($UserList=Cache::get("UserList.id.".$uid)){
							$RoleUser[$key]=$UserList;
						}
					}
					$result['role_user'][$rid]=$RoleUser;
				}
			}
		}
		$_SESSION=$result;
		echo "<pre>";
		var_dump($_SESSION);
	}
}

if(!$result){
	//錯誤3次要鎖30分鐘
	if($login_try >= 3){
		echo "{$time}秒解鎖";
	}else{
		Mcache::$con->add($memcache_key_count,0);
		Mcache::$con->increment($memcache_key_count);
		Mcache::$con->set($memcache_key_time,time());
		echo "嘗試第".++$login_try."次";
	}	
}



