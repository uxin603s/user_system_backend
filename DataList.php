<?php
class DataList{
	public static $table='data_list';
	public static $filter_field_arr=['id','name','wid','sort_id'];
	public static $cache_key_field=['wid','id'];
	use CRUD{
		CRUD::flushCache as private tmp_flushCache;	
	}
	public static function flushCache(){
		self::tmp_flushCache();
		UserList::reset_session();
	}
}