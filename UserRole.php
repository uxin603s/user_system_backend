<?php
class UserRole{
	public static $table='user_role';
	public static $filter_field_arr=['uid','rid'];
	use CRUD;
	public static function flushCache(){
		$tmp=self::getList(null);
		$UserRole=[];
		$RoleUser=[];
		if($tmp['status']){	
			foreach($tmp['list'] as $item){
				$UserRole[$item['uid']][]=$item['rid'];
				$RoleUser[$item['rid']][]=$item['uid'];
			}
		}
		
		Cache::run("UserRole",$UserRole);
		Cache::run("RoleUser",$RoleUser);
		UserList::reset_session();
	}
}
