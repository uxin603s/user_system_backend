<?php
//檔案快取
class Fcache{
	//存入的路徑設定

	public static $path;
	private function __construct(){
		self::$path=__DIR__."/.Fcache/";
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
		
		file_put_contents($dir_path,json_encode($cache_data));

		return true;
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
	
	//功能:刪除所有快取
	public static function del_all(){
		
		$where=self::where();
		
		foreach($where as $key=>$val){
			self::del($key);
		}
		return $where;
	}
	
	//功能:以鍵值搜尋快取資料
	public static function where($preg="",$where=[],$not_where=[]){
		
		self::init();
		$path=self::$path;
		
		
		exec("find {$path} -name '*' -type f ",$data);
		
		
		
		$result=[];		
		foreach($data as $key_name){
			$start=strrpos($key_name,"/")+1;
			$len=strlen($key_name);
			$count=$len-$start;
			$key_name=substr($key_name,$start,$count);
			
			if((strpos($key_name,".")===0))continue;
			
			if(!$preg || (strpos($key_name,$preg)===0) || @preg_match($preg,$key_name,$match)){
				
				if($match){
					foreach($not_where as $field=>$array){
						if($match[$field] && in_array($match[$field],$array)){
							continue 2;
						}
					}
					foreach($where as $field=>$array){
						if($match[$field] && !in_array($match[$field],$array)){
							continue 2;
						}
					}
				}
				self::lock($key_name);
				if($value=self::get($key_name)){
					$result[$key_name]=$value;
				}
				self::unlock($key_name);
			}
			
			unset($match);
		}	
		
		return $result;
	}
	
	public static $lock=[];
	public static function lock($cache_key){
		$dir_path=self::get_dir($cache_key);
		$path_arr=[];
		$path_arr[]=$dir_path;
		$path_arr[]=$key_name;
		$dir_path=implode("/",$path_arr);
		if(file_exists($dir_path)){
			$f=fopen($dir_path,'rw');    
			flock($f,LOCK_EX);
			self::$lock[$cache_key]=$f;
		}
	}
	public static function unlock($cache_key){
		if(self::$lock[$cache_key]){
			// flock(self::$lock[$cache_key], LOCK_UN);
			fclose(self::$lock[$cache_key]);
		}
	}
	
}