<?php
//檔案快取
class Fcache{
	//存入的路徑設定

	public static $path;
	private function __construct(){
		self::$path=__DIR__."/.Fcache/";
		if(empty(self::$path)){
			throw Exception("need set path");
		}
	}
	
	public static function init(){
		if(is_null(self::$path))new self;					
	}
	//功能:取得資料夾名稱 $key_name鍵值
	private static function get_dir($key_name){
		self::init();
		$md5_key=md5($key_name);
		$len=strlen($md5_key);
		$count=3;
		$path_arr=[];
		$path_arr[]=self::$path;
		$path_arr[]=substr($md5_key,0,$count);
		// $path_arr[]=substr($md5_key,$count,$len-3);
		return implode("/",$path_arr);		
	}
	//功能:設定快取 $key_name鍵值
	public static function get($key_name){
		self::init();
		$dir_path=self::get_dir($key_name);
		$path_arr=[];
		$path_arr[]=$dir_path;
		$path_arr[]=$key_name;
		$dir_path=implode("/",$path_arr);
		if(file_exists($dir_path)){			
			$data=file_get_contents($dir_path);
			return json_decode($data,1);			
		}
		return false;		
	}
	//功能:設定快取 $cache_key快取鍵值 $cache_data快取資料
	public static function set($key_name,$cache_data){
		self::init();
		$dir_path=self::get_dir($key_name);
		$path_arr=[];
		$path_arr[]=$dir_path;
		if(!file_exists($dir_path)){
			mkdir($dir_path,0777,true);
		}
		$path_arr[]=$key_name;
		$dir_path=implode("/",$path_arr);
		return file_put_contents($dir_path,json_encode($cache_data));							
	}
	//功能:刪除快取 $cache_key快取鍵值  
	public static function del($key_name){
		self::init();
		$dir_path=self::get_dir($key_name);
		$path_arr=[];
		$path_arr[]=$dir_path;		
		$path_arr[]=$key_name;
		$dir_path=implode("/",$path_arr);
		if(file_exists($dir_path)){		
			return unlink($dir_path);
		}
		return false;
	}
	//功能:以鍵值搜尋快取資料
	public static function where($where="",$key_type=0){
		self::init();
		$path=self::$path;
		$data=[];
		
		exec("find {$path} -name '*' -type f ",$data);
		$result=[];		
		foreach($data as $key_name){
			$start=strrpos($key_name,"/")+1;
			$len=strlen($key_name);
			$count=$len-$start;
			$key_name=substr($key_name,$start,$count);
			
			if((strpos($key_name,".")===0))continue;
			
			if(!$where || (strpos($key_name,$where)===0) || @preg_match($where,$key_name)){
				if($value=self::get($key_name)){
					if($key_type){
						$result[$key_name]=$value;
					}else{
						$result[]=$value;
					}
				}
			}
		}						
		return $result;
	}
	//功能:刪除所有快取
	public static function del_all(){
		
		$where=self::where();
		foreach($where as $key=>$val){
			self::del($key);
		}
		return $where;
	}
}