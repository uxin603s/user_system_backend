<?php
include_once __DIR__."/include.php";
include_once __DIR__."/FbRegisterList.php";
include_once __DIR__."/UserList.php";
include_once __DIR__."/WebList.php";
include_once __DIR__."/RoleList.php";
include_once __DIR__."/DataList.php";
include_once __DIR__."/RoleData.php";
include_once __DIR__."/UserRole.php";

session_start();
session_write_close();

if(isset($_SESSION['uid']) && $_SESSION['uid']==0){
	
}else{
	$status=false;
	$message="權限不足";
	$result=compact(['status',"message"]);
	echo json_encode($result);
	exit;
}

include_once __DIR__."/github/MysqlCompact/API.php";