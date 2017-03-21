<?php
include_once __DIR__."/include.php";

$tmp=IPList::getList();
$white=[];
$black=[];
if($tmp['status']){
	foreach($tmp['list'] as $val){
		$ip=$val['ip'];
		if($val['status']==0){
			$black[$ip]=$val;
		}else if($val['status']==1){
			$white[$ip]=$val;
		}
		if($val['keep_time_int']!=-1)
		if($val['keep_time_int']<$_SERVER['REQUEST_TIME']){
			$where=[];
			$where["ip"]=$val['ip'];
			IPList::delete($where);
		}
	}
}

if(isset($white[$_SERVER['REMOTE_ADDR']])){
	if(isset($_GET['ip'])){
		$_SERVER['REMOTE_ADDR']=$_GET['ip'];
	}
}



//白名單直接給最高權限
//黑名單
$status=true;
if(isset($white[$_SERVER['REMOTE_ADDR']])){
	$data['rid']=[0];
	$data['name']=$_SERVER['REMOTE_ADDR'];
	$status=true;
	$ip=$_SERVER['REMOTE_ADDR'];
	
	echo json_encode(compact(["status","data","message","ip"]));
	exit;
}else if(isset($black[$_SERVER['REMOTE_ADDR']])){
	
	$keep_time_int=$black[$_SERVER['REMOTE_ADDR']]['keep_time_int'];
	$count=$black[$_SERVER['REMOTE_ADDR']]['count'];	
	
	if($count>3 && $keep_time_int>$_SERVER['REQUEST_TIME']){//被封鎖中
		$status=false;
		echo "<pre>";
		var_dump("錯誤超過3次封鎖");
	}
	
	if($keep_time_int==-1){
		echo "<pre>";
		var_dump("永ban");
		$status=false;
	}else if($keep_time_int<$_SERVER['REQUEST_TIME']){
		$where=[];
		$where["ip"]=$_SERVER['REMOTE_ADDR'];
		IPList::delete($where);
		$status=true;
		echo "<pre>";
		var_dump("時間過了解除封鎖");
	}
	
}
	


if($status){
	if(mb_strlen($_GET['access_token'])==32 && preg_match("/^[a-z0-9]+$/",$_GET['access_token'])){
		$data=UserList::compactUser($_GET['access_token']);
		$status=true;
		$message="成功取得資料";
	}else{
		if(isset($black[$_SERVER['REMOTE_ADDR']])){
			$update=[];
			$update['keep_time_int']=$_SERVER['REQUEST_TIME']+30*60;
			$update['count']=++$black[$_SERVER['REMOTE_ADDR']]['count'];
			$where=[];
			$where["ip"]=$_SERVER['REMOTE_ADDR'];
			IPList::update(compact(["where","update"]));
			var_dump("count={$black[$_SERVER['REMOTE_ADDR']]['count']}");
		}else{
			$insert=[];
			$insert['keep_time_int']=$_SERVER['REQUEST_TIME']+30*60;
			$insert['count']=1;
			$insert["ip"]=$_SERVER['REMOTE_ADDR'];
			IPList::insert($insert);
			var_dump("count=1");
		}
		
		$status=false;
	}
}



$ip=$_SERVER['REMOTE_ADDR'];

echo json_encode(compact(["status","data","message","ip"]));