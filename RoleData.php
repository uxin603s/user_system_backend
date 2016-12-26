<?php
class RoleData{
	public static $table='role_data';
	public static $filter_field_arr=['rid','did','aid','action'];
	use CRUD;
	public static function flushData(){
		$tmp=self::getList(null);
		$RoleData=[];
		if($tmp['status']){
			foreach($tmp['list'] as $item){
				if($tmp=Cache::get("DataList.id.".$item['did'])){
					$RoleData[$item['rid']][$tmp][$item['action']]=$item['aid'];
				}
			}		
		}
		Cache::run("RoleData",$RoleData,1);
		UserList::reset_session();
	}
}