<?php
class FbRegisterList{
	public static $table='fb_register_list';
	public static $filter_field_arr=['id','name','email','gender','created_time_int','status','uid'];
	public static $cache_key_field=null;
	use CRUD;
}