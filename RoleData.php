<?php
class RoleData{
	public static $table='role_data';
	public static $filter_field_arr=['rid','did','aid','action'];
	use CRUD;
}