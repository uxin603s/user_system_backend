<?php
class UserList{
	public static $table='user_list';
	public static $filter_field_arr=['id','name','status','fb_id','created_time_int'];
	use CRUD;
}