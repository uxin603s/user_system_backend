<?php
include_once __DIR__."/include.php";

if($Config['local']==1){
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
	$result=json_decode(file_get_contents($url),1);
	$access_token=$result['access_token'];
	ob_get_clean();
		
	if($access_token){
		$url="https://graph.facebook.com/me?fields=id,name,gender,email&access_token=".$access_token;
		ob_start();
		$json=file_get_contents($url);
		ob_get_clean();
		$fb_data=json_decode($json,1);
		$where_list=[
			['field'=>"id",'type'=>0,'value'=>$fb_data['id']],
			// ['field'=>"status",'type'=>0,'value'=>1],
		];
		$result=FbRegisterList::getList(compact(['where_list']));
		$status=false;
		if($result['status']){
			if($result['list'][0]['uid']==0 || $result['list'][0]['status']==0){
				$view['message']="請等待管理員審核";
			}else{
				$where_list=[
					['field'=>"id",'type'=>0,'value'=>$result['list'][0]['uid']],
				];
				$result=UserList::getList(compact(['where_list']));
				if($result['status']){
					$status_arr=["空缺","在職","離職"];
					if($result['list'][0]['status']==1){
						$status=true;
						$access_token=$result['list'][0]['access_token'];
						
						
					}else{
						$view['message']="";
						$view['message'].="目前狀態為".$status_arr[$result['list'][0]['status']];
						$view['message'].="無法使用，請聯絡管理員!!!";
					}
				}else{
					$view['message']="請等待管理員審核";
				}
			}
		}else{
			$insert['status']=0;
			$insert['created_time_int']=time();
			$insert['id']=$fb_data['id'];
			$insert['name']=$fb_data['name'];
			$insert['gender']=$fb_data['gender'];
			$insert['email']=$fb_data['email'];
			FbRegisterList::insert($insert);
			$view['message']="謝謝你的註冊，請等待管理員審核";
		}
		
		if(isset($_COOKIE['go_to'])){
			if($status){
				header("location:{$_COOKIE['go_to']}?access_token={$access_token}");
			}else{
				header("location:{$_COOKIE['go_to']}?error=".$view['message']);
			}
			setcookie("go_to","",time()-3600);
			
		}else{
			if($status){
				$_REQUEST['access_token']=$access_token;
				UserSystemHelp::login();
			}else{
				$view['id']=$fb_data['id'];
				$view['name']=$fb_data['name'];
				echo View::set(__DIR__."/view/login.html",$view);
			}
		}
	}else{
		echo "<pre>";
		var_dump($http_response_header);
		header("location:http://{$_SERVER['HTTP_HOST']}/{$_SERVER['PHP_SELF']}");
	}
}else{
	$scope=urlencode("email");
	$auth_type="rerequest";
	$go_where="https://www.facebook.com/v2.3/dialog/oauth?client_id={$client_id}&redirect_uri={$redirect_uri}&scope={$scope}&auth_type={$auth_type}";
	header("location:".$go_where);
}
