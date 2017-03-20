<?php
class UserSystemHelp{
	public static $location=true;
	public static $local=false;
	public static function getConfig(){
		$path=__DIR__."/config.json";
		if(file_exists($path)){
			return json_decode(file_get_contents($path),1);
		}else{
			throw new Exception("config不存在");
		}
	}
	public static function login($success="UserSystemHelp::success",$error="UserSystemHelp::error"){
		$config=self::getConfig();
		$base_path=$config['base_path'];
		$white_path=$config['white_path'];
		
		if(self::$local){
			$data['rid']=[0];
			UserSystemHelp::success($data);
			exit;
		}
		if(isset($_REQUEST['access_token'])){
			if(mb_strlen($_REQUEST['access_token'])==32 && preg_match("/^[a-z0-9]+$/",$_REQUEST['access_token'])){
				$ip=$_SERVER['REMOTE_ADDR'];
				$url="{$base_path}/api.php?access_token={$_REQUEST['access_token']}&ip={$ip}";
				ob_start();
				$result=json_decode(file_get_contents($url),1);
				ob_get_clean();
				
				if(preg_match("/\d{3}/",$http_response_header[0],$match)){
					$http_code=$match[0];
				}
				if($http_code!=200){
					$result=Fcache::get("userSystem_{$_REQUEST['access_token']}");
				}
				if(is_callable($success) && $result['status']){
					call_user_func($success,$result['data']);
				}
			}else{
				$result=[];
				$result['status']=false;
				$result['message']="access_token不符合規定";
			}
			setcookie("access_token","",time()-60*60);
			
			if(is_callable($error) && !$result['status']){
				call_user_func($error,$result['message']);
			}
			
		}
		elseif(isset($_REQUEST['error'])){
			if(is_callable($error)){
				call_user_func($error,$result['error']);
			}
		}
		else{
			$go_to=urlencode("http://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}");
			$url="{$base_path}/login.php?go_to={$go_to}";
			header("location: {$url}");
		}
	}
	public static function success($data){
		
		$access_token=$data['access_token'];
		
		if($data['go_to'] && is_numeric(strpos("http://{$_SERVER['HTTP_HOST']}",$data['go_to']))){
			$go_to=$data['go_to'];
		}else if($_COOKIE['go_to']){
			$go_to=$_COOKIE['go_to'];
		}else{
			$go_to="index.php";
		}
		
		//寫入session
		session_start();
		$_SESSION=$data;
		$data['session_id']=session_id();
		$data['REMOTE_ADDR']=$_SERVER['REMOTE_ADDR'];
		session_write_close();
		
		//data寫入快取方便主站掛點時利用 及主站 刷新外部網站用
		
		Fcache::set("userSystem_{$access_token}",$data);
		setcookie("access_token",$access_token,time()+60*60);
		//data找導頁資料並導頁
		if(self::$location){
			header("location: {$go_to}");
			exit;
		}
		
	}
	
	public static function error($message){
		return $message;
		// 錯誤頁面顯示
		var_dump($message);
	}
	public static function flushData(){
		$config=self::getConfig();
		$base_path=$config['base_path'];
		$white_path=$config['white_path'];
		
		if($_SERVER['REMOTE_ADDR']==$white_path){
			$list=Fcache::where("userSystem_");
			//找access_token與session_id關聯
			//並重新回主站要資料重新寫入
			
			foreach($list as $item){
				$access_token=$item['access_token'];
				if($item['session_id']){
					session_id($item['session_id']);
					
					$_REQUEST['access_token']=$access_token;
					self::$location=false;
					self::login();
				}else{
					Fcache::del("userSystem_{$access_token}");
				}

			}
		}
	}
	public static function checkSession(){
		if(isset($_SESSION['access_token'])){
			$data=Fcache::get("userSystem_{$_SESSION['access_token']}");
			
			$message=[];
			$status=true;
			if(session_id()!=$data['session_id']){
				$status=false;
				$message[]="session_id不等於";
			}
			if($_SERVER['REMOTE_ADDR']!=$data['REMOTE_ADDR']){
				$status=false;
				$message[]="REMOTE_ADDR不等於";
			}
			if(!$status){
				session_destroy();
				$message=implode(",",$message);
				$reload=1;
				$result=compact(['status',"message","reload","data"]);
				echo json_encode($result);
				exit;
			}
		}
	}
}