<?php
//記憶體快取
class Mcache{
	public static $con=null;
	//前綴詞
	public static $prefix;
	private function __construct(){
		self::$con=new Memcached();
		self::$con->addServer('127.0.0.1',11211);

		if(empty(self::$prefix)){
			throw Exception("need set prefix");
		}
		self::$con->setOption(Memcached::OPT_PREFIX_KEY,self::$prefix);
	}
	public static function getStats(){
		if(is_null(self::$con))new self;
		$stats=self::$con->getStats();
		return $stats;
	}
	public static function getResultCode(){
		if(is_null(self::$con))new self;
		$code=self::$con->getResultCode();
		return $code;
	}
	//保持一個連線
	public static function init(){
		if(is_null(self::$con))new self;
	}	
	//功能:取得快取 $cache_key:快取鍵值 $keep:延長時間
	public static function get($cache_key,$keep=0){
		self::init();
		$cache_data=self::$con->get($cache_key);
		if($keep){			
			self::set($cache_key,$cache_data,$keep);
		}
		return $cache_data;
	}
	//功能:設定快取 $cache_key快取鍵值 $cache_data快取資料 $second快取時間
	public static function set($cache_key,$cache_data,$second=0,$lock=false){
		self::init();	
		if($lock){
			return self::cas($cache_key,$cache_data,$second);
		}else{
			return self::$con->set($cache_key,$cache_data,$second);
		}
	}
	
	//功能:刪除快取 $cache_key快取鍵值  $second刪除時間
	public static function del($cache_key,$second=0){
		// echo $cache_key;
		self::init();
		return self::$con->delete($cache_key,$second);
	}
	//功能:刪除有prefix的資料
	public static function del_all(){
		$list=self::where();
		foreach($list as $key=>$val){	
			self::del($key);
		}	
		return $list;		
	}	
	//功能:以鍵值搜尋快取資料
	public static function where($preg="",$where=[],$not_where=[]){
		self::init();
		$result=[];
		if($all_keys=self::$con->getAllKeys()){
			
			foreach($all_keys as $key_name){
				if(strpos($key_name,self::$prefix)===0){
					$start=strlen(self::$prefix);
					$count=strlen($key_name);
					$key_name=substr($key_name,$start,$count);
					
					if(!$preg || (strpos($key_name,$preg)===0) || preg_match($preg,$key_name,$match)){
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
						if($value=self::get($key_name)){
							$result[$key_name]=$value;
						}
					}
					unset($match);
				}
			}
		}		
		return $result;						
	}
	public static function cas($cache_key,$callback,$second=0){
		do {
			$tmp = self::$con->get($cache_key, null, $cas);
			if($callback){
				$cache_data=$callback($tmp);
			}
			if (self::$con->getResultCode() == Memcached::RES_NOTFOUND) {
				self::$con->add($cache_key,$cache_data,$second);
			} else { 
				$result=self::$con->cas($cas,$cache_key,$cache_data,$second);
			}   
		}while (self::$con->getResultCode() != Memcached::RES_SUCCESS);
		return $result;
	}

}
