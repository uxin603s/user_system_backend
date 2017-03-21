<?php
Class IPList{
	public static $table='ip_list';
	public static $filter_field_arr=["ip","count","status","keep_time_int","last_time_int"];
	use CRUD{
		 CRUD::insert as private tmp_insert;
	}
	public static function insert($arg){
		$arg['last_time_int']=time();
		return static::tmp_insert($arg);
	}
}