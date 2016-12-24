<?php
class UserSystemHelp{
	public static function login($success="",$cache="",$error=""){
		if(isset($_GET['access_token'])){
			if(mb_strlen($_GET['access_token'])==32 && preg_match("/^[a-z0-9]+$/",$_GET['access_token'])){
				$ip=$_SERVER['REMOTE_ADDR'];
				$url="http://user.cfd888.info/api.php?access_token={$_GET['access_token']}&ip={$ip}";
				ob_start();
				$result=json_decode(file_get_contents($url),1);
				ob_get_clean();
				
				if(preg_match("/\d{3}/",$http_response_header[0],$match)){
					$http_code=$match[0];
				}
				if($http_code!="200"){		
					$result=[];
					if(is_callable($cache)){
						$result['status']=true;
						$result['data']=call_user_func($cache,$_GET['access_token']);
						$result['message']="主站掛點使用快取";
					}else{
						$result['status']=false;
						$result['message']="無快取處理";
					}
				}
				if(is_callable($success) && $result['status']){
					call_user_func($success,$result['data']);
				}
			}else{
				$result=[];
				$result['status']=false;
				$result['message']="access_token不符合規定";
			}
			
			
			if(is_callable($error) && !$result['status']){
				call_user_func($error,$result['message']);
			}
			
		}else{
			header("location: http://user.cfd888.info/login.php?goto=".$_SERVER['HTTP_REFERER']);
		}
	}
	public static function success($data){
		
		$_SESSION=$data;
		//data寫入快取方便主站掛點時利用
		//data找導頁資料並導頁
		//把access_token與session_id建立關聯寫進快取
		//主站刷新 外部網站 刷新用
		
	}
	public static function cache($access_token){
		//取得data快取return
	}
	public static function error($message){
		// 去錯誤頁面
	}
	public static function flushData(){
		if(in_array($_SERVER['REMOTE_ADDR'],["192.168.1.1"])){
			//找access_token與session_id關聯
			//並重新回主站要資料重新寫入
		}
	}
}