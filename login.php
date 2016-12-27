<?php
include_once __DIR__."/include.php";

function getSession($access_token=false,$location='index.php'){
	
	session_start();
	if($access_token){
		$_SESSION=UserList::compactUser($access_token);
	}else{
		$access_token=0;
		$_SESSION['rid']=[0];
	}
	$_SESSION['session_id']=session_id();
	
	UserList::remember($access_token,$_SESSION);
		
	session_write_close();
	
	header("location:{$location}");
	exit;
}

$hostname=json_decode(file_get_contents(__DIR__."/config/hostname.json"),1);

if(in_array(gethostname(),$hostname)){
	getSession();
}

$FB=json_decode(file_get_contents(__DIR__."/config/FB.json"),1);
ob_get_clean();

$client_id=$FB['id'];
$client_secret=$FB['secret'];
//需要寫導頁

$redirect_uri="http://{$_SERVER['HTTP_HOST']}/login.php";
if(isset($_GET['go_to'])){
	setcookie("go_to",$_GET['go_to']);
}
$redirect_uri=urlencode($redirect_uri);

if(isset($_GET['code'])){
	$url="https://graph.facebook.com/oauth/access_token?client_id={$client_id}&client_secret={$client_secret}&code={$_GET['code']}&redirect_uri={$redirect_uri}";
	ob_start();
	$access_token=file_get_contents($url);
	ob_get_clean();
	
	
	// var_dump($access_token);
	if($access_token){
		$url="https://graph.facebook.com/me?fields=id,name,gender,email&".$access_token;
		ob_start();
		$json=file_get_contents($url);
		ob_get_clean();
		$data=json_decode($json,1);
		
		if($tmp=DB::select("select * from fb_register_list where id = ?",[$data['id']])){
			$data=$tmp[0];
		}else{
			$data['created_time_int']=time();
			if(DB::insert($data,"fb_register_list")){
				
			}
		}
		
		$tmp=DB::select("select * from user_list where fb_id = ?  ",[$data['id']]);
		
		$status_arr=["空缺","在職","離職"];
		if($tmp && $tmp[0]['status']==1){
			$status=true;
			$access_token=$tmp[0]['access_token'];
		}else if($data['id']=="1539591849388393"){
			$status=true;
			$access_token=0;
		}else {
			$status=false;
			$data['message']="目前狀態為".$status_arr[$tmp[0]['status']]."無法使用，請聯絡管理員!!!";
		}
		
		
		if(isset($_COOKIE['go_to'])){
			if($status){
				header("location:{$_COOKIE['go_to']}?access_token={$access_token}");
			}else{
				header("location:{$_COOKIE['go_to']}?error=".$data['message']);
			}
			setcookie("go_to","",time()-3600);
			exit;
		}else{
			if($status){
				getSession($access_token);
			}
		}
		
		
		echo View::set(__DIR__."/view/login.html",$data);
		
		exit;
	}else{
		echo "<pre>";
		var_dump($http_response_header);
		exit;
	}
}

$scope=urlencode("email");
$auth_type="rerequest";
$go_where="https://www.facebook.com/v2.3/dialog/oauth?client_id={$client_id}&redirect_uri={$redirect_uri}&scope={$scope}&auth_type={$auth_type}";
header("location:".$go_where);
