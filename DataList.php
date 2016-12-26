<?php
class DataList{
	public static $table='data_list';
	public static $filter_field_arr=['id','name','wid','sort_id'];
	use CRUD;
	public static function flushCache(){
		$tmp=self::getList(null);
		$DataList=[];
		if($tmp['status']){
			foreach($tmp['list'] as $item){
				$DataList[$item['id']]=$item['name'];
			}		
		}
		Cache::run("DataList",$DataList);
		UserList::reset_session();
	}
}