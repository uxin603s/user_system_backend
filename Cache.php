<?php
//Mcache及Fcache合用
class Cache{
	//功能:取得快取 $cache_key:快取鍵值 $keep:延長時間
	public static function get($cache_key,$keep=0){		
		if(!$data=Mcache::get($cache_key,$keep)){		
			if($data=Fcache::get($cache_key)){
				Mcache::set($cache_key,$data,$keep);
				// echo 'F';
			}
		}else{
			// echo 'M';
		}
		return $data;
	}
	//功能:設定快取 $cache_key快取鍵值 $cache_data快取資料 $second快取時間
	public static function set($cache_key,$cache_data,$second=0){
		Mcache::set($cache_key,$cache_data,$second);
		Fcache::set($cache_key,$cache_data);		
	}
	//功能:刪除快取 $cache_key快取鍵值  $second刪除時間
	public static function del($cache_key,$second=0){
		Mcache::del($cache_key,$second);
		Fcache::del($cache_key);	
	}
	//功能:刪除所有資料
	public static function del_all(){
		Mcache::del_all();
		Fcache::del_all();	
	}
	//功能:以鍵值搜尋快取資料
	public static function where($where=""){
		if(!($data=Mcache::where($where))){
			$data=Fcache::where($where);
		}
		return $data;	
	}
}
