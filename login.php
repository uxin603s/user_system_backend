<?php
include_once __DIR__."/include.php";

if($Config['location']==1){
	UserSystemHelp::$local=true;
	UserSystemHelp::login();
}
$client_id=$Config['FB.id'];
$client_secret=$Config['FB.secret'];

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
			DB::insert($data,"fb_register_list");			
		}
		
		$tmp=DB::select("select * from user_list where fb_id = ?  ",[$data['id']]);
		
		$status_arr=["空缺","在職","離職"];
		if($tmp && $tmp[0]['status']==1){
			$status=true;
			$access_token=$tmp[0]['access_token'];
		}else{
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
				$_REQUEST['access_token']=$access_token;
				UserSystemHelp::login();
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
