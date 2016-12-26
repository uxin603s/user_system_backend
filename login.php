<?php
include_once __DIR__."/include.php";

function getSession($access_token=false,$location='index.php'){
	
	session_start();
	if($access_token){
		$_SESSION=UserList::compactUser($access_token);
	}else{
		$_SESSION['rid']=[0];
		
	}
	$_SESSION['session_id']=session_id();
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
if(isset($_GET['goto'])){
	$redirect_uri.="?goto={$_GET['goto']}";
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
			if(DB::insert($data,"fb_register_list")){
				
			}
		}
		
		if(true && $data['id']=="1539591849388393"){
			getSession();
		}else{
			if($tmp=DB::select("select * from user_list where fb_id = ?  ",[$data['id']])){
				$status_arr=["空缺","在職","離職"];
				if($tmp[0]['status']==1){
					if(isset($_GET['goto'])){
						$goto="{$_GET['goto']}&access_token={$tmp[0]['access_token']}";
					}else{
						$goto="index.php";
					}
					getSession($tmp[0]['access_token'],$goto);
				}else{
					$data['message']="，目前狀態為".$status_arr[$tmp[0]['status']]."無法使用，請聯絡管理員!!!";
				}
			}
			// var_dump($tmp);
			// exit;
			if(isset($_GET['goto'])){
				header("location:{$_GET['goto']}&error=".$data['message']);
				exit;
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
