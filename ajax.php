<?php
include_once __DIR__."/include.php";


session_start();
session_write_close();

if(isset($_SESSION['rid']) && in_array(0,$_SESSION['rid'])){
	
}else{
	$status=false;
	$message="權限不足";
	$result=compact(['status',"message"]);
	echo json_encode($result);
	exit;
}

include_once __DIR__."/github/MysqlCompact/API.php";