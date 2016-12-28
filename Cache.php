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
	
	public static function run($name,$list){
		$new=array_keys($list);
		
		$old=[];
		if($tmp=self::get($name.".list")){
			$old=$tmp;
		}
		
		
		self::set($name.".list",$new,60*30);
		foreach($new as $key){
			self::set($name.".id.".$key,$list[$key],60*30);
		}
		$del=array_diff($old,$new);
		foreach($del as $key){
			self::del($name.".id.".$key);
		}
		
		$new_list=self::get_all($name);
		return $new_list;
	}
	public static function get_all($name){
		$ids=self::get($name.".list");
		$result=[];
		if($ids)
		foreach($ids as $id){
			$result[$id]=self::get_one($name,$id);
		}
		return $result;
	}
	public static function get_one($name,$id){
		return self::get($name.".id.".$id);
	}
}
