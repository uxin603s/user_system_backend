<?php
class RoleData{
	public static $table='role_data';
	public static $filter_field_arr=['rid','did','aid','action'];
	public static $cache_key_field=['rid','did','aid','action'];
	use CRUD{
		CRUD::flushCache as private tmp_flushCache;	
	}
	public static function flushCache(){
		self::tmp_flushCache();
		UserList::reset_session();
	}
}