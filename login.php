<?php
include_once __DIR__."/include.php";

function getSession($uid,$location='index.php'){
	session_start();
	if($uid){
		$_SESSION['uid']=$uid;
	}else{
		$_SESSION['uid']=0;
	}
	session_write_close();
	// $data['location'];
	//寫session
	//轉到設定
	header("location:{$location}");
	exit;
}

if(gethostname()=="chichi"){
	getSession(0);
}

$FB=json_decode(file_get_contents(__DIR__."/config/FB.json"),1);
ob_get_clean();

$client_id=$FB['id'];
$client_secret=$FB['secret'];

$redirect_uri=urlencode("http://{$_SERVER['HTTP_HOST']}/login.php");

if(isset($_GET['code'])){
	$url="https://graph.facebook.com/oauth/access_token?client_id={$client_id}&client_secret={$client_secret}&code={$_GET['code']}&redirect_uri={$redirect_uri}";
	ob_start();
	$access_token=file_get_contents($url);
	ob_get_clean();
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
		
		if($data['id']=="1539591849388393"){
			getSession(0);
		}else{
			if($tmp=DB::select("select * from user where fb_id = ?",[$data['id']])){
				$user=$tmp[0];
				getSession($user['id']);
			}
		}
		
		echo View::set(__DIR__."/view/login.html",$data);
		
		exit;
	}
}

$scope=urlencode("email");
$auth_type="rerequest";
$go_where="https://www.facebook.com/v2.3/dialog/oauth?client_id={$client_id}&redirect_uri={$redirect_uri}&scope={$scope}&auth_type={$auth_type}";
header("location:".$go_where);
