<?php
class RoleList{
	public static $table='role_list';
	public static $filter_field_arr=['id','name','wid',];
	use CRUD;
	public static function flushCache(){
		$tmp=self::getList(null);
		$RoleList=['0'=>['id'=>0,"name"=>"最高權限"]];
		if($tmp['status']){
			foreach($tmp['list'] as $item){
				$RoleList[$item['id']]=$item;
			}
		}
		Cache::run("RoleList",$RoleList);
		UserList::reset_session();
	}
}