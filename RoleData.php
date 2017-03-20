<?php
class RoleData{
	public static $table='role_data';
	public static $filter_field_arr=['rid','did','aid','action'];
	public static $cache_key_field=['rid','did','aid','action'];
	use CRUD{
	}
	
}