<?php
class WebList{
	public static $table='web_list';
	public static $filter_field_arr=['id','name','status','fb_id','created_time_int'];
	use CRUD;
	public static function flushCache(){
		$tmp=self::getList(null);
		$WebList=[];
		if($tmp['status']){	
			foreach($tmp['list'] as $item){
				$WebList[$item['id']]=$item;
			}
		}
		Cache::run("WebList",$WebList);
		UserList::reset_session();
	}
}