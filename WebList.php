<?php
class WebList{
	public static $table='web_list';
	public static $filter_field_arr=['id','name'];
	public static $cache_key_field=['id'];
	use CRUD{
		CRUD::flushCache as private tmp_flushCache;	
	}
	public static function flushCache($arg,$type){
		self::tmp_flushCache($arg,$type);
		UserList::reset_session();
	}
}