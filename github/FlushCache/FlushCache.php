<?php
class FlushCache{
	public static function add_del($new,$old,$name,$list){
		// $add=array_diff($new,$old);
		foreach($new as $key){
			self::add_callback($name,$list,$key);
		}
		$del=array_diff($old,$new);
		foreach($del as $key){
			self::del_callback($name,$list,$key);
		}
		// var_dump($add,$del);
	}
	public static function add_callback($name,$list,$key){
		// var_dump($name.$key,$list[$key]);
		Cache::set($name.$key,$list[$key],60*30);
	}
	public static function del_callback($name,$list,$key){
		Cache::del($name.$key);
	}
	
	public static function run($name,$list,$flush=false){
		$new=array_keys($list);
		$old=[];
		if(!$flush){
			if($tmp=Cache::get($name.".list")){
				$old=$tmp;
			}
		}
		
		Cache::set($name.".list",$new,60*30);
		
		FlushCache::add_del($new,$old,$name.".id.",$list);
		$new_list=FlushCache::get_all($name);
		return $new_list;
	}
	public static function get_all($name){
		$ids=Cache::get($name.".list");
		$result=[];
		foreach($ids as $id){
			$result[]=Cache::get($name.".id.".$id);
		}
		return $result;
	}
	public static function get($name,$id){
		return Cache::get($name.".id.".$id);
	}
}