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
		FlushCache::run("UserRole",$UserRole,1);
		FlushCache::run("RoleUser",$RoleUser,1);
	}
}