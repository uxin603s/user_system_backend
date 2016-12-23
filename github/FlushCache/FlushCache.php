<?php
class FlushCache{
	public static function add_del($new,$old,$name,$list){
		$add=array_diff($new,$old);
		foreach($add as $key){
			self::add_callback($name,$list,$key);
		}
		$del=array_diff($old,$new);
		foreach($del as $key){
			self::del_callback($name,$list,$key);
		}
	}
	public static function add_callback($name,$list,$key){
		// var_dump($name,$list,$key);
		Cache::set($name.$key,$list[$key],60*30);
	}
	public static function del_callback($name,$list,$key){
		Cache::del($name.$key);
	}
	public static function check_data($ids,$name){
		$result=[];
		foreach($ids as $id){
			$result[]=Cache::get($name.$id);
		}
		return $result;
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
		$new_list=FlushCache::check_data($new,$name.".id.");
		// var_dump($new_list);
		return $new_list;
	}
}