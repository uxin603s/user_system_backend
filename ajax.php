<?php
include_once __DIR__."/include.php";

session_start();
$tmp=Fcache::get("userSystem_{$_SESSION['access_token']}");
if(session_id()==$tmp['session_id'] && $tmp['REMOTE_ADDR']==$_SERVER['REMOTE_ADDR']){

}else{
	session_destroy();
	$status=false;
	$message="ggwp";
	$reload=1;
	$result=compact(['status',"message","reload"]);
	echo json_encode($result);
	exit;
}

if(isset($_SESSION['rid']) && in_array(0,$_SESSION['rid'])){
	
}else{
	$status=false;
	$message="權限不足";
	$reload=1;
	$result=compact(['status',"message","reload"]);
	echo json_encode($result);
	exit;
}

include_once __DIR__."/github/MysqlCompact/API.php";