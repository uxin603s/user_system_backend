<?php
class UserRole{
	public static $table='user_role';
	public static $filter_field_arr=['uid','rid'];
	use CRUD;
}