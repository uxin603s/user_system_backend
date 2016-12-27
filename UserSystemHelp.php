<?php
class UserSystemHelp{
	public static function login($success="",$error="",$location=true){
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
				if(is_callable($success) && $result['status']){
					call_user_func($success,$result['data'],$location);
				}
			}else{
				$result=[];
				$result['status']=false;
				$result['message']="access_token不符合規定";
			}
			
			
			if(is_callable($error) && !$result['status']){
				call_user_func($error,$result['message']);
			}
			
		}
		elseif(isset($_GET['error'])){
			if(is_callable($error)){
				call_user_func($error,$result['error']);
			}
		}
		else{
			$go_to=urlencode("http://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}");
			$url="http://user.cfd888.info/login.php?go_to={$go_to}";
			header("location: {$url}");
		}
	}
	public static function success($data,$location){
		
		$access_token=$data['access_token'];
		
		
		if($data['go_to'] && is_numeric(strpos("http://{$_SERVER['HTTP_HOST']}",$data['go_to']))){
			$go_to=$data['go_to'];
		}else{
			$go_to="/index.php";
		}
		
		//寫入session
		session_start();
		$_SESSION=$data;
		$data['session_id']=session_id();
		session_write_close();
		
		//data寫入快取方便主站掛點時利用 及主站 刷新外部網站用
		Fcache::set("userSystem_{$access_token}",$data,60*30);
		
		//data找導頁資料並導頁
		if($location){
			header("location: {$go_to}");
		}
		
	}
	
	public static function error($message){
		return $message;
		// 錯誤頁面顯示
		var_dump($message);
	}
	public static function flushData(){
		$white_ip=json_decode(file_get_contents(__DIR__."/white_ip.json"),1);
		if(in_array($_SERVER['REMOTE_ADDR'],$white_ip)){
			$list=Fcache::where("userSystem_");
			//找access_token與session_id關聯
			//並重新回主站要資料重新寫入
			
			foreach($list as $item){
				$access_token=$item['access_token'];
				if($item['session_id']){
					session_id($item['session_id']);
					
					$_GET['access_token']=$access_token;
					self::login("UserSystemHelp::success","UserSystemHelp::error",false);
				}else{
					Fcache::del("userSystem_{$access_token}");
				}
			}
		}
	}
}