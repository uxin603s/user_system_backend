<?php
class WebList{
	public static $table='web_list';
	public static $filter_field_arr=['id','name'];
	public static $cache_key_field=['id'];
	use CRUD{
	}
}