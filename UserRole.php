<?php
class UserRole{
	public static $table='user_role';
	public static $filter_field_arr=['uid','rid'];
	public static $cache_key_field=['uid','rid'];
	use CRUD{
		CRUD::flushCache as private tmp_flushCache;	
	}
	public static function flushCache(){
		self::tmp_flushCache();
		UserList::reset_session();
	}
}
