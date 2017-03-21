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
$message_arr=[];
$status=true;
if(isset($white[$_SERVER['REMOTE_ADDR']])){
	$data['rid']=[0];
	$data['name']=$_SERVER['REMOTE_ADDR'];
	$status=true;
	$ip=$_SERVER['REMOTE_ADDR'];
	$message="白名單";
	echo json_encode(compact(["status","data","message","ip"]));
	exit;
}else if(isset($black[$_SERVER['REMOTE_ADDR']])){
	
	$keep_time_int=$black[$_SERVER['REMOTE_ADDR']]['keep_time_int'];
	$count=$black[$_SERVER['REMOTE_ADDR']]['count'];	
	
	if($count>3 && $keep_time_int>$_SERVER['REQUEST_TIME']){//被封鎖中
		$status=false;
		$message_arr[]="錯誤超過3次封鎖";
	}
	
	if($keep_time_int==-1){
		$message_arr[]="永ban";
		$status=false;
	}else if($keep_time_int<$_SERVER['REQUEST_TIME']){
		$where=[];
		$where["ip"]=$_SERVER['REMOTE_ADDR'];
		IPList::delete($where);
		$status=true;
		$message_arr[]="時間過了解除封鎖";
		
	}
	
}
	


if($status){
	if(mb_strlen($_GET['access_token'])==32 && preg_match("/^[a-z0-9]+$/",$_GET['access_token'])){
		$data=UserList::compactUser($_GET['access_token']);
		$status=true;
		$message_arr[]="成功取得資料";
	}else{
		$keep_time_int=$_SERVER['REQUEST_TIME']+30*60;
		if(isset($black[$_SERVER['REMOTE_ADDR']])){
			$count=$black[$_SERVER['REMOTE_ADDR']]['count'];;
			$update=[];
			$update['keep_time_int']=$keep_time_int;
			$update['count']=++$count;
			$where=[];
			$where["ip"]=$_SERVER['REMOTE_ADDR'];
			IPList::update(compact(["where","update"]));
		}else{
			$count=1;
			$insert=[];
			$insert['keep_time_int']=$keep_time_int;
			$insert['count']=$count;
			$insert["ip"]=$_SERVER['REMOTE_ADDR'];
			IPList::insert($insert);
		}
		
		$status=false;
		$message_arr[]="嘗試第{$count}次";
	}
}


$message=implode("，",$message_arr);
$ip=$_SERVER['REMOTE_ADDR'];

echo json_encode(compact(["status","data","message","ip"]));