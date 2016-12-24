<?php
class SessionSW{
	public static function set($id,$data=[]){
		if($id){
			session_id($id);
			session_start();
			if(is_array($data)){
				foreach($data as $key=>$val){
					$_SESSION[$key]=$val;
				}
			}else{
				$_SESSION=$data;
			}
			session_write_close();
			// return $_SESSION;
			return self::get($id);
		}
		return false;
	}
	public static function get($id){
		
		if($id){
			session_id($id);
			session_start();
			session_write_close();
			return $_SESSION;
		}
		return false;
	}
}
// $a=SessionSW::set("s7tcpag8bbtp41vro7590pflk7",["qq"=>3]);
// $b=SessionSW::set("f9te7odt3jhn6ekqtis91fil31",["qq"=>4]);
// $a=SessionSW::get("s7tcpag8bbtp41vro7590pflk7",["qq"=>3]);
// $b=SessionSW::get("f9te7odt3jhn6ekqtis91fil31",["qq"=>4]);

// echo "<pre>";
// var_dump($a,$b);
// exit;