<?php
include_once __DIR__."/include.php";


session_start();

if($Config['location']==1){
	
}else{
	if(isset($_SESSION['rid']) && in_array(0,$_SESSION['rid'])){
		
	}else{
		$status=false;
		$message="權限不足";
		$reload=1;
		$result=compact(['status',"message","reload"]);
		echo json_encode($result);
		exit;
	}
}

include_once __DIR__."/github/MysqlCompact/API.php";